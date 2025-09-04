#!/usr/bin/env bash

set -ex

if test $# = 0; then
    paths='.'
else
    paths="$@"
fi

npm exec eslint $paths
