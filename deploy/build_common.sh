#!/bin/bash

set -e

INDIR=
OUTDIR=

error() {
    >&2 echo "error: $@"
}

warning() {
    >&2 echo "warning: $@"
}

die() {
    error "$@"
    exit 1
}

usage() {
    local code="$1"
    cat <<EOF
usage: $PROGNAME [OPTIONS]

Options:
    -o  output directory
    -i  input directory
    -h  show this help
EOF
    exit $code
}

input_args() {
    [ -z "$1" ] && usage

    while [[ $# -gt 0 ]]; do
        case $1 in
            -o)
                OUTDIR="$2"
                shift
                ;;
            -i)
                INDIR="$2"
                shift
                ;;
            -h)
                usage
                ;;
            *)
                die "unexpected argument: $1"
                ;;
        esac
        shift
    done
}

check_args() {
    [ -z "$OUTDIR" ] && {
        error "output directory not specified"
        usage 1
    }
    [ -z "$INDIR" ] && {
        error "input directory not specified"
        usage 1
    }

    if [ ! -d "$OUTDIR" ]; then
        mkdir "$OUTDIR"
    else
        # warning "$OUTDIR already exists, erasing it"
        rm "$OUTDIR"/*
    fi
}