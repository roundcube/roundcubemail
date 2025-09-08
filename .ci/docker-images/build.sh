#!/bin/bash

case "$1" in
	''|-*)
		echo "Error: first and only argument must be the wanted PHP version."
		echo "E.g.: $(basename $0) 8.4"
		exit 1
		;;
	*)
		phpversion="$1"
		;;
esac

exec docker build --build-arg "PHPVERSION=$phpversion" -f "$(realpath $(dirname $0)/Dockerfile)" -t "localhost/roundcubemail-testrunner:php$phpversion" "$(realpath $(dirname $0)/../../..)"
