#!/bin/bash

# The script is intended for use on Travis with Trusty distribution
# It executes unit and functional tests

cd ..

if [ "$CODE_COVERAGE" = 1 ]
then
    CODE_COVERAGE_ARGS="--coverage-text";
fi

vendor/bin/phpunit -c tests/phpunit.xml $CODE_COVERAGE_ARGS

if [ "$BROWSER_TESTS" = 1 ] && [ $? = 0 ]
then
    .ci/setup.sh \
    && TESTS_MODE=desktop vendor/bin/phpunit -c tests/Browser/phpunit.xml \
    && TESTS_MODE=phone vendor/bin/phpunit -c tests/Browser/phpunit.xml \
    && TESTS_MODE=tablet vendor/bin/phpunit -c tests/Browser/phpunit.xml
fi
