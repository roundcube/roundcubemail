#!/bin/bash -ex

if ! test -f config/config-test.inc.php; then
	cp -v .ci/config-test.inc.php config/config-test.inc.php
fi

# Install dependencies, prefer highest.
composer update $COMPOSER_ARGS --prefer-dist --no-interaction --no-progress

# Execute tests.
vendor/bin/phpunit -c ./tests/MessageRendering/phpunit.xml --fail-on-warning --fail-on-risky
