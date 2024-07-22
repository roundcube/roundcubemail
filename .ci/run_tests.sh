#!/bin/bash -ex

if ! test -f config/config-test.inc.php; then
	cp -v .ci/config-test.inc.php config/config-test.inc.php
fi

composer require "kolab/net_ldap3:~1.1.4" --no-update

# Install dependencies, prefer highest.
composer update --prefer-dist --no-interaction --no-progress

# Execute tests.
vendor/bin/phpunit -c tests/phpunit.xml --fail-on-warning --fail-on-risky

# Downgrade dependencies to the lowest versions.
composer update --prefer-dist --prefer-stable --prefer-lowest --no-interaction --no-progress --optimize-autoloader

# Execute tests again.
vendor/bin/phpunit -c tests/phpunit.xml --fail-on-warning --fail-on-risky
