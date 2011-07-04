#!/bin/sh
JS_DIR=`dirname "$0"`/../program/js

if [ ! -d "$JS_DIR" ]; then
	echo "Directory $JS_DIR not found."
	exit 1
fi

for fn in app common googiespell list; do
	if [ -r "$JS_DIR/${fn}.js.src" ]; then
		mv "$JS_DIR/${fn}.js.src" "$JS_DIR/${fn}.js"
		echo "Reverted $JS_DIR/${fn}.js"
	fi
done
