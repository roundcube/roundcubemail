#!/bin/sh
JS_DIR=`dirname "$0"`/../program/js
CLOSURE_COMPILER_URL='http://closure-compiler.googlecode.com/files/compiler-latest.zip'

do_shrink() {
	rm -f "$2"
	java -jar compiler.jar --compilation_level=SIMPLE_OPTIMIZATIONS --js="$1" --js_output_file="$2"
}

if [ ! -d "$JS_DIR" ]; then
	echo "Directory $JS_DIR not found."
	exit 1
fi

if java -version >/dev/null 2>&1; then
	:
else
	echo "Java not found. Please ensure that the 'java' program is in your PATH."
	exit 1
fi

if [ ! -r "compiler.jar" ]; then
	if which wget >/dev/null 2>&1 && which unzip >/dev/null 2>&1; then
		wget "$CLOSURE_COMPILER_URL" -O "/tmp/$$.zip"
	elif which curl >/dev/null 2>&1 && which unzip >/dev/null 2>&1; then
		curl "$CLOSURE_COMPILER_URL" -o "/tmp/$$.zip"
	else
		echo "Please download $CLOSURE_COMPILER_URL and extract compiler.jar to this directory."
		exit 1
	fi
	unzip "/tmp/$$.zip" "compiler.jar"
	rm -f "/tmp/$$.zip"
fi

for fn in app common googiespell list; do
	if [ -r "$JS_DIR/${fn}.js.src" ]; then
		echo "$JS_DIR/${fn}.js.src already exists, not overwriting"
	else
		mv "$JS_DIR/${fn}.js" "$JS_DIR/${fn}.js.src"
	fi
	echo "Shrinking $JS_DIR/${fn}.js"
	do_shrink "$JS_DIR/${fn}.js.src" "$JS_DIR/${fn}.js"
done
