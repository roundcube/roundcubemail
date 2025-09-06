#!/bin/bash

location=$(dirname $0) 

basedir=$(cd "$location/.." && pwd)

commit() {
    cd "$basedir"
    git pull
    echo "// commit $(pwd)"
    git add --all  :/
    git status 
    message=$(git status --short)
    git commit --message "$message"
}

commit
