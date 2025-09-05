#!/usr/bin/env bash

set -ex

composer update --prefer-dist --no-interaction --no-progress
./vendor/bin/phpstan --memory-limit=1G analyse -v $@
