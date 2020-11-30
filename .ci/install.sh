#!/bin/bash

# The script is intended for use on Travis with Trusty distribution

DIR=$(dirname $0)

# Enable xdebug for code coverage
if [ "$CODE_COVERAGE" != 1 ]; then phpenv config-rm xdebug.ini || true; fi

cd $DIR/..

cp composer.json-dist composer.json

# Add laravel/dusk for Browser tests
if [ "$BROWSER_TESTS" = 1 ]; then composer require "laravel/dusk:~6.9.0" --no-update; fi

# Add suggested dependencies required for tests
composer require "kolab/net_ldap3:~1.1.1" --no-update

# Install PHP dependencies
composer install --prefer-dist

# Install Less for Elastic CSS compilation
npm install --force -g less
npm install --force -g less-plugin-clean-css

# Roundcube tests and instance configuration
cp .ci/config-test.inc.php config/config-test.inc.php
