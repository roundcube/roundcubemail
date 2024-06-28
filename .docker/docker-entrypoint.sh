#!/bin/bash -ex

# This script is intended to run on development code, so it installs
# dependencies etc, in case this is the first run on this code base.

composer require -n "nesbot/carbon:^2.62.1" --no-update
composer require -n "laravel/dusk:^7.9" --no-update
composer update -n --prefer-dist --no-interaction --no-progress

if ! test -f config/config-test.inc.php; then
	cp -v .github/config-test.inc.php config/config-test.inc.php
fi

# Install Javascript production dependencies if not present (using the primary
# one as indicator).
if ! test -f ./program/js/jquery.min.js; then
	bin/install-jsdeps.sh
fi

# Install development tools.
npm install

# Compile CSS if not present.
if ! test -f skins/elastic/styles/styles.min.css; then
	# Compile Elastic's styles
	npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/styles.less > skins/elastic/styles/styles.min.css
	npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/print.less > skins/elastic/styles/print.min.css
	npx lessc --clean-css="--s1 --advanced" skins/elastic/styles/embed.less > skins/elastic/styles/embed.min.css
fi

# if ! test -f program/js/app.min.js; then
# 	# Use minified javascript files
# 	bin/jsshrink.sh
# fi

# if ! test -f vendor/laravel/dusk/bin/chromedriver-linux; then
# 	# Install proper WebDriver version for installed browser version
# 	php tests/Browser/install.php $(chromium --version | tr -cd [:digit:].)
# fi

export TESTS_MODE
exec $@
