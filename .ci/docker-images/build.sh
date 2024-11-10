#!/bin/bash

case "$1" in
	8.1|8.3)
		phpversion="$1"
		;;
	*)
		echo "Error: first and only argument must be the wanted PHP version."
		echo "Usage: $(basename $0) 8.1|8.3"
		exit 1
		;;
esac

exec docker build --build-arg "PHPVERSION=$phpversion" -f "$(realpath $(dirname $0)/Dockerfile)" -t "ghcr.io/pabzm/roundcubemail-testrunner:php$phpversion" "$(realpath $(dirname $0)/../../..)"
