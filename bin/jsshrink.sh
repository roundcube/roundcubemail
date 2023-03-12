#!/bin/sh

set -e

PWD=`dirname "$0"`
LANG_IN='ECMASCRIPT5'

do_shrink() {
    rm -f "$2"
    # copy the first comment block with license information for LibreJS
    grep -q '@lic' $1 && sed -n '/\/\*/,/\*\// { p; /\*\//q; }' $1 > $2
    uglifyjs --compress --mangle -- $1 >> $2
}

if which uglifyjs > /dev/null 2>&1; then
    :
else
    echo "uglifyjs not found. Please install e.g. 'npm install -g uglify-js'."
    exit 1
fi

# compress single file from argument
if [ $# -gt 0 ]; then
    JS_FILE="$1"

    if [ $# -gt 1 ]; then
        LANG_IN="$2"
    fi

    echo "Shrinking $JS_FILE"
    minfile=`echo $JS_FILE | sed -e 's/\.js$/\.min\.js/'`
    do_shrink "$JS_FILE" "$minfile" "$LANG_IN"
    exit
fi

DIRS="$PWD/../program/js $PWD/../skins/* $PWD/../plugins/* $PWD/../plugins/*/skins/* $PWD/../plugins/managesieve/codemirror/lib"
# default: compress application scripts
for dir in $DIRS; do
    for file in $dir/*.js; do
        if echo "$file" | grep -q -e '.min.js$'; then
            continue
        fi
        if [ ! -f "$file" ]; then
            continue
        fi

        echo "Shrinking $file"
        minfile=`echo $file | sed -e 's/\.js$/\.min\.js/'`
        do_shrink "$file" "$minfile" "$LANG_IN"
    done
done
