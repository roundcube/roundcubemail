#!/bin/bash

# The script is intended for use on Travis with Trusty distribution

DIR=$(dirname $0)
GMV=1.5.11

# Roundcube tests and instance configuration
cp $DIR/config-test.inc.php $DIR/../config/config-test.inc.php

# In-Browser tests dependencies installation
# and GreenMail server setup and start
wget http://central.maven.org/maven2/com/icegreen/greenmail-standalone/$GMV/greenmail-standalone-$GMV.jar \
    && (sudo java -Dgreenmail.setup.all -Dgreenmail.users=test:test -jar greenmail-standalone-$GMV.jar &) \
    && sleep 5
