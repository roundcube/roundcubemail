#!/bin/bash

# The script is intended for use on Travis with Trusty distribution

set -x

GMV=1.5.11
CHROMEVERSION=$(google-chrome-stable --version | tr -cd [:digit:]. | cut -d . -f 1)

# Roundcube tests and instance configuration
cp .ci/config-test.inc.php config/config-test.inc.php

# Make temp and logs writeable
sudo chmod 777 temp logs

# Install javascript dependencies
bin/install-jsdeps.sh

# Compile Elastic's styles
sudo apt-get install -y node-less

lessc skins/Elastic/styles/styles.less > skins/Elastic/styles/styles.css
lessc skins/Elastic/styles/print.less > skins/Elastic/styles/print.css
lessc skins/Elastic/styles/embed.less > skins/Elastic/styles/embed.css

# Install proper WebDriver version for installed Chrome browser
php tests/Browser/install.php $CHROMEVERSION

# In-Browser tests dependencies installation
# and GreenMail server setup and start
wget http://central.maven.org/maven2/com/icegreen/greenmail-standalone/$GMV/greenmail-standalone-$GMV.jar \
    && (sudo java -Dgreenmail.setup.all -Dgreenmail.users=test:test -jar greenmail-standalone-$GMV.jar &) \
    && sleep 5
