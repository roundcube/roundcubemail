#!/bin/bash

TITLE="RoundCube Classes"
PACKAGES="Core"

PATH_PROJECT=$PWD/program/include
PATH_DOCS=$PWD/doc/phpdoc
PATH_PHPDOC=/usr/local/php5/bin/phpdoc

OUTPUTFORMAT=HTML
CONVERTER=frames
TEMPLATE=earthli
PRIVATE=off

# make documentation
$PATH_PHPDOC -d $PATH_PROJECT -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE

