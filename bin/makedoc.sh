#!/usr/bin/env bash

if [ -z "$SSH_TTY" ]
then
  if [ -z "$DEV_TTY" ]
  then
    echo "Not on the shell."
    exit 1
  fi
fi

TITLE="RoundCube Classes"
PACKAGES="Core"

INSTALL_PATH="`dirname $0`/.."
PATH_PROJECT=$INSTALL_PATH/program/include
PATH_DOCS=$INSTALL_PATH/doc/phpdoc
BIN_PHPDOC="`/usr/bin/which phpdoc`"

if [ ! -x "$BIN_PHPDOC" ]
then
  echo "phpdoc not found: $BIN_PHPDOC"
  exit 1
fi

OUTPUTFORMAT=HTML
CONVERTER=frames
TEMPLATE=earthli
PRIVATE=off

# make documentation
$BIN_PHPDOC -d $PATH_PROJECT -t $PATH_DOCS -ti "$TITLE" -dn $PACKAGES \
-o $OUTPUTFORMAT:$CONVERTER:$TEMPLATE -pp $PRIVATE

