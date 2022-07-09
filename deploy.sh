#!/bin/bash

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

DEV_DIR="${DIR}"
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

$PHP composer.phar install --no-dev --optimize-autoloader
$PHP prepare_static.php

cp "$DEV_DIR/config-local.php" .
cat config-local.php  | grep -v is_dev | tee config-local.php
popd

# copy staging to prod
rsync -a --delete --delete-excluded --info=progress2 "$STAGING_DIR/" "$PROD_DIR/" \
    --exclude .git \
    --exclude debug.log \
    --exclude='/composer.*' \
    --exclude='/htdocs/scss' \
    --exclude='/htdocs/sass.php' \
    --exclude='*.sh'
