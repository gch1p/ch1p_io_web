#!/bin/bash

PROGNAME="$0"
DIR="$( cd "$( dirname "$(readlink -f "${BASH_SOURCE[0]}")" )" && pwd )"
ROOT="$(realpath "$DIR/../")"
CLEANCSS="$ROOT"/node_modules/clean-css-cli/bin/cleancss

. $DIR/build_common.sh

build_scss() {
    local entry_name="$1"
    local theme="$2"

    local input="$INDIR/entries/$entry_name/$theme.scss"
    local output="$OUTDIR/$entry_name"
    [ "$theme" = "dark" ] && output="${output}_dark"
    output="${output}.css"

    sassc -t compressed "$input" "$output"
}

cleancss() {
    local entry_name="$1"
    local theme="$2"

    local file="$OUTDIR/$entry_name"
    [ "$theme" = "dark" ] && file="${file}_dark"
    file="${file}.css"

    $CLEANCSS -O2 "all:on;mergeSemantically:on;restructureRules:on" "$file" > "$file.tmp"
    rm "$file"
    mv "$file.tmp" "$file"
}

create_dark_patch() {
    local entry_name="$1"
    local light_file="$OUTDIR/$entry_name.css"
    local dark_file="$OUTDIR/${entry_name}_dark.css"

    "$DIR"/gen_css_diff.js "$light_file" "$dark_file" > "$dark_file.diff"
    rm "$dark_file"
    mv "$dark_file.diff" "$dark_file"
}

THEMES="light dark"
TARGETS="common admin"

input_args "$@"
check_args

[ -x "$CLEANCSS" ] || die "cleancss is not found"

for theme in $THEMES; do
    for target in $TARGETS; do
        build_scss "$target" "$theme"
    done
done

for target in $TARGETS; do
    create_dark_patch "$target"
    for theme in $THEMES; do cleancss "$target" "$theme"; done
done