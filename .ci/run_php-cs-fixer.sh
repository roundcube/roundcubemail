#!/usr/bin/env bash

set -ex

composer update --prefer-dist --no-interaction --no-progress
vendor/bin/php-cs-fixer fix --dry-run --using-cache=no --diff --verbose $@
