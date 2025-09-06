#!/bin/bash

location=$(dirname $0) 

basedir=$(cd "$location/.." && pwd)

composer="$basedir/composer.json"

version_get() {
    cat "$composer" | grep '"version":' | sed -r -e 's/^.*([0-9]+[.][0-9]+[.][0-9]+).*$/\1/'
}

version_put() {
    local version="$1"
    sed -i -r -e 's/(^.*"version":.*)([0-9]+[.][0-9]+[.][0-9]+)(.*$)/\1'${version}'\3/' "$composer"
}

version_split() {
    local version="$1"
    local array=(${version//'.'/' '})
    version_major=${array[0]}    
    version_minor=${array[1]}    
    version_micro=${array[2]}    
}

version_build() {
    echo "${version_major}.${version_minor}.${version_micro}"
}

version_increment() {
    version_micro=$(( $version_micro + 1 ))
}

version_update() {
    version=$(version_get)
    version_split "$version"
    version_increment
    version=$(version_build)
    version_put "$version"
}

project_release() {
    cd "$basedir"
    echo "// commit $(pwd)"
    git add --all  :/
    git status 
    message=$(git status --short)
    git commit --message "$message"
    tag="$version"
    git tag -a "$tag" -m "release version $version"
    git push
    git push --tags
}

###

version_update

project_release
