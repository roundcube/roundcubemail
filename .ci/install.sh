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

# phpunit v7 is working fine on PHP8, but composer installs an older version,
# so we'll emulate PHP 7.4 platform to get phpunit v7
if [[ ${TRAVIS_PHP_VERSION:0:1} == "8" ]]; then composer config platform.php 7.4; fi

# Install PHP dependencies
composer install --prefer-dist

# Install Less for Elastic CSS compilation, and UglifyJS for JS files minification
if [ "$BROWSER_TESTS" = 1 ]
then
    npm install --force -g less
    npm install --force -g less-plugin-clean-css
    npm install --force -g uglify-js
fi

# Roundcube tests and instance configuration
cp .ci/config-test.inc.php config/config-test.inc.php
