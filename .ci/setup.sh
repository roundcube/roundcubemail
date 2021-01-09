#!/bin/bash

# The script is intended for use on Travis with Trusty distribution
# It installs in-browser tests dependencies and prepares Roundcube instance

GMV=1.5.11
CHROMEVERSION=$(google-chrome-stable --version | tr -cd [:digit:]. | cut -d . -f 1)
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
    && sleep 5
