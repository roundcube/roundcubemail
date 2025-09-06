#!/bin/sh

# Download and install the Sabre\Vobject library for this plugin

wget 'https://github.com/fruux/sabre-vobject/archive/2.1.0.tar.gz' -O sabre-vobject-2.1.0.tar.gz
tar xf sabre-vobject-2.1.0.tar.gz

mv sabre-vobject-2.1.0/lib/* .
rm -rf sabre-vobject-2.1.0

cd lib/Sabre/VObject && wget --no-check-certificate -O Property.php https://raw2.github.com/thomascube/sabre-vobject/84b64c65f9a94f7ec5a5e327bab3cc1335dd613c/lib/Sabre/VObject/Property.php

