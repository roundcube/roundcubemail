#!/bin/bash

# The script is intended for use on Travis with Trusty distribution

DIR=$(dirname $0)

# Enable xdebug for code coverage
if [ "$CODE_COVERAGE" != 1 ]; then phpenv config-rm xdebug.ini || true; fi

cd $DIR/..

cp composer.json-dist composer.json

# Add laravel/dusk for Browser tests
if [ "$BROWSER_TESTS" = 1 ]; then composer require "laravel/dusk:~5.9.1" --no-update; fi

# Remove qr-code as it requires php-gd which is not always available on Travis
# and we don't really need it for tests
composer remove endroid/qr-code --no-update

# Install PHP dependencies
composer install --prefer-dist
