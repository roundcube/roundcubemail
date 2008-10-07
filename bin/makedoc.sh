#!/bin/bash

TITLE="RoundCube Classes"
PACKAGES="Core"

INSTALL_PATH="`dirname $0`/.."
PATH_PROJECT=$INSTALL_PATH/program/include
PATH_DOCS=$INSTALL_PATH/doc/phpdoc

if [ -x /usr/local/php5/bin/phpdoc ]
then
  PATH_PHPDOC=/usr/local/php5/bin/phpdoc
elif [ -x /usr/bin/phpdoc ]
then
  PATH_PHPDOC=/usr/bin/phpdoc
else
  echo "phpdoc not found"
  exit 1
fi

OUTPUTFORMAT=HTML
CONVERTER=frames
TEMPLATE=earthli
PRIVATE=off

# make documentation
$PATH_PHPDOC -d $PATH_PROJECT -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE

