#!/bin/bash

# The script is intended for use on Travis with Trusty distribution
# It installs in-browser tests dependencies and prepares Roundcube instance

GMV=1.6.13
CHROMEVERSION=$(google-chrome-stable --version | tr -cd [:digit:].)
GMARGS="-Dgreenmail.setup.all -Dgreenmail.users=test:test -Dgreenmail.startup.timeout=3000"

# Make temp and logs writeable
sudo chmod 777 temp logs

# Install javascript dependencies
bin/install-jsdeps.sh

# Compile Elastic's styles
lessc --clean-css="--s1 --advanced" skins/elastic/styles/styles.less > skins/elastic/styles/styles.min.css
lessc --clean-css="--s1 --advanced" skins/elastic/styles/print.less > skins/elastic/styles/print.min.css
lessc --clean-css="--s1 --advanced" skins/elastic/styles/embed.less > skins/elastic/styles/embed.min.css

# Use minified javascript files
bin/jsshrink.sh

# Install proper WebDriver version for installed Chrome browser
php tests/Browser/install.php $CHROMEVERSION

# GreenMail server download, setup and start
wget https://repo1.maven.org/maven2/com/icegreen/greenmail-standalone/$GMV/greenmail-standalone-$GMV.jar \
    && (sudo java $GMARGS -jar greenmail-standalone-$GMV.jar &) \
    && sleep 10

# Run tests
echo "TESTS_MODE: DESKTOP" \
&& TESTS_MODE=desktop vendor/bin/phpunit -c tests/Browser/phpunit.xml --exclude-group=failsonga \
&& echo "TESTS_MODE: TABLET" \
&& TESTS_MODE=tablet vendor/bin/phpunit -c tests/Browser/phpunit.xml --exclude-group=failsonga-tablet \
# Mobile mode tests are unreliable on Github Actions
# && echo "TESTS_MODE: PHONE" \
# && TESTS_MODE=phone vendor/bin/phpunit -c tests/Browser/phpunit.xml --exclude-group=failsonga-phone \
