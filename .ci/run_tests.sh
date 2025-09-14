#!/bin/bash -ex

if ! test -f config/config-test.inc.php; then
	cp -v .ci/config-test.inc.php config/config-test.inc.php
fi

composer require $COMPOSER_ARGS "kolab/net_ldap3:~1.1.4" --no-update

# Install dependencies, prefer highest.
composer update $COMPOSER_ARGS --prefer-dist --no-interaction --no-progress

# Execute tests.
vendor/bin/phpunit -c tests/phpunit.xml --fail-on-warning --fail-on-risky --display-deprecations

# Downgrade dependencies to the lowest versions.
composer update $COMPOSER_ARGS --prefer-dist --prefer-stable --prefer-lowest --no-interaction --no-progress --optimize-autoloader

# Execute tests again. We do not want this run to fail due to deprecations though, because those can only occur from
# (older) dependencies, which we migh still consider good enough.
vendor/bin/phpunit -c tests/phpunit.xml --fail-on-warning --fail-on-risky --display-deprecations --do-not-fail-on-deprecation
