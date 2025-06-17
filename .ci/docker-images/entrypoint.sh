#!/bin/bash -x

if [[ ! -f /app/index.php ]]; then
    echo "Error: No source code in /app – you must mount your code base to that path!"
    exit 1
fi

rsync -a --delete --exclude .git --exclude node_modules --exclude vendor /app/ /work/

cd /work


exec $@
