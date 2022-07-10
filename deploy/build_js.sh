#!/bin/bash

PROGNAME="$0"
DIR="$( cd "$( dirname "$(readlink -f "${BASH_SOURCE[0]}")" )" && pwd )"

. $DIR/build_common.sh

# suckless version of webpack
# watch and learn, bitches!
build_chunk() {
    local name="$1"
    local output="$OUTDIR/$name.js"
    local not_first=0
    for file in "$INDIR/$name"/*.js; do
        # insert newline before out comment
        [ "$not_first" = "1" ] && echo "" >> "$output"
        echo "/* $(basename "$file") */" >> "$output"

        cat "$file" >> "$output"
        not_first=1
    done
}

TARGETS="common admin"

input_args "$@"
check_args

for f in $TARGETS; do
    build_chunk "$f"
done