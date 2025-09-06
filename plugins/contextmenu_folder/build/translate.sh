#!/bin/bash

# http://docs.transifex.com/client/setup/

location=$(dirname $0) 

basedir=$(cd "$location/.." && pwd)

# http://docs.transifex.com/client/pull/
translate_pull() {
    echo "$FUNCNAME"
    cd "$basedir"
    tx pull --all
}

# http://docs.transifex.com/client/push/
translate_push() {
    echo "$FUNCNAME"
    cd "$basedir"
    tx push --source --translations
}

translate_pull

translate_push
