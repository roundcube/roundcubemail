#!/bin/bash -ex

# The script is intended for use locally, as well as in the CI.
# It runs the browser-tests ("E2E" in the CI).
# It expects a running IMAP server (connection configured in
# `config-test.inc.php`, and a running Chrome/Chromium browser (connection
# hard-coded in code, overrideable via environment variables).

# Make temp and logs writeable to everyone.
chmod 777 temp logs

# Create downloads dir and ensure permissions (if it's set, the variable might
# be blank if tests are not run using containers).
if test -n "$TESTRUNNER_DOWNLOADS_DIR"; then
	# Use sudo because in the Github action we apparently can't use a
	# directory in $HOME or /tmp but another one for which we need
	# superuser-rights.
	install -m 777 -d "$TESTRUNNER_DOWNLOADS_DIR"
fi

if ! test -f config/config-test.inc.php; then
	cp -v .ci/config-test.inc.php config/config-test.inc.php
fi

# Install dependencies for to remote control the browser.
composer require $COMPOSER_ARGS -n "nesbot/carbon:^2.62.1" --no-update
composer require $COMPOSER_ARGS -n "laravel/dusk:^7.9" --no-update

if $(echo $PHP_VERSION | grep -q '^8.3'); then
	# Downgrade dependencies (for PHP 8.3 only)
	composer update $COMPOSER_ARGS --prefer-dist --prefer-stable --prefer-lowest --no-interaction --no-progress --optimize-autoloader
else
	composer update $COMPOSER_ARGS --prefer-dist --no-interaction --no-progress
fi

# Install development tools.
npm install

# Install javascript dependencies
bin/install-jsdeps.sh

# Compile Elastic's styles
make css-elastic

# Use minified javascript files
bin/jsshrink.sh

# Run tests
echo "TESTS_MODE: DESKTOP"
TESTS_MODE=desktop vendor/bin/phpunit -c tests/Browser/phpunit.xml --fail-on-warning --fail-on-risky --exclude-group=failsonga

echo "TESTS_MODE: TABLET"
TESTS_MODE=tablet vendor/bin/phpunit -c tests/Browser/phpunit.xml --fail-on-warning --fail-on-risky --exclude-group=failsonga-tablet

# Mobile mode tests are unreliable on Github Actions
# echo "TESTS_MODE: PHONE"
# TESTS_MODE=phone vendor/bin/phpunit -c tests/Browser/phpunit.xml --fail-on-warning --fail-on-risky --exclude-group=failsonga-phone
