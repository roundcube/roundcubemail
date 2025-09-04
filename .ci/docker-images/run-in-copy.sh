#!/usr/bin/env bash

set -ex

mkdir -p /work
rsync -a /app/ /work/
$@
