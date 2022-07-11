#!/bin/bash

die() {
    >&2 echo "error: $@"
    exit 1
}

set -e

DIR="$( cd "$( dirname "$(readlink -f "${BASH_SOURCE[0]}")" )" && pwd )"

DEV_DIR="$(realpath "$DIR/../")"
STAGING_DIR="$HOME/staging"
PROD_DIR="$HOME/prod"
PHP=/usr/bin/php8.1

git push origin master

[ -d "$STAGING_DIR" ] || mkdir "$STAGING_DIR"
pushd "$STAGING_DIR"

if [ ! -d .git ]; then
    git init
    git remote add origin git@ch1p.io:ch1p_io_web.git
    git fetch
    git checkout master
fi

git reset --hard
git pull origin master

composer8.1 install --no-dev --optimize-autoloader --ignore-platform-reqs

if [ ! -d node_modules ]; then
    npm i
fi

cp "$DEV_DIR/config-local.php" .
sed -i '/is_dev/d' ./config-local.php

"$DIR"/build_js.sh -i "$DEV_DIR/htdocs/js" -o "$STAGING_DIR/htdocs/dist-js" || die "build_js failed"
"$DIR"/build_css.sh -i "$DEV_DIR/htdocs/scss" -o "$STAGING_DIR/htdocs/dist-css" || die "build_css failed"
$PHP "$DIR"/gen_static_config.php -i "$STAGING_DIR/htdocs" > "$STAGING_DIR/config-static.php" || die "gen_static_config failed"

popd

# copy staging to prod
rsync -a --delete --delete-excluded --info=progress2 "$STAGING_DIR/" "$PROD_DIR/" \
    --exclude .git \
    --exclude debug.log \
    --exclude='/composer.*' \
    --exclude='/htdocs/scss' \
    --exclude='/htdocs/js' \
    --exclude='/htdocs/sass.php' \
    --exclude='/htdocs/js.php' \
    --exclude='*.sh' \
    --exclude='*.sql'
