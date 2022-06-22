GITREMOTE=git://github.com/roundcube/roundcubemail.git
GITBRANCH=release-1.5
GPGKEY=devs@roundcube.net
VERSION=1.5.3
 
all: clean complete dependent framework
 
complete: roundcubemail-git
	cp -RH roundcubemail-git roundcubemail-$(VERSION)
	(cd roundcubemail-$(VERSION); jq '.require += {"kolab/net_ldap3": "~1.1.1"} | del(.suggest."kolab/net_ldap3")' --indent 4 composer.json-dist > composer.json)
	(cd roundcubemail-$(VERSION); cp composer.json composer.json-bak; /tmp/composer.phar config platform.php 5.5; /tmp/composer.phar require symfony/polyfill-intl-idn:1.19.0 --no-install)
	(cd roundcubemail-$(VERSION); php /tmp/composer.phar install --prefer-dist --no-dev --ignore-platform-reqs)
	(cd roundcubemail-$(VERSION); mv composer.json-bak composer.json)
	(cd roundcubemail-$(VERSION); bin/install-jsdeps.sh --force)
	(cd roundcubemail-$(VERSION); bin/jsshrink.sh program/js/publickey.js; bin/jsshrink.sh plugins/managesieve/codemirror/lib/codemirror.js)
	(cd roundcubemail-$(VERSION); rm jsdeps.json bin/install-jsdeps.sh *.orig; rm -rf vendor/masterminds/html5/test vendor/pear/*/tests vendor/*/*/.git* vendor/pear/crypt_gpg/tools vendor/pear/console_commandline/docs vendor/pear/mail_mime/scripts vendor/pear/net_ldap2/doc vendor/pear/net_smtp/docs vendor/pear/net_smtp/examples vendor/pear/net_smtp/README.rst vendor/endroid/qrcode/tests temp/js_cache)
	tar czf roundcubemail-$(VERSION)-complete.tar.gz roundcubemail-$(VERSION)
	rm -rf roundcubemail-$(VERSION)

dependent: roundcubemail-git
	cp -RH roundcubemail-git roundcubemail-$(VERSION)
	tar czf roundcubemail-$(VERSION).tar.gz roundcubemail-$(VERSION)
	rm -rf roundcubemail-$(VERSION)
 
framework: roundcubemail-git /tmp/phpDocumentor.phar
	cp -r roundcubemail-git/program/lib/Roundcube roundcube-framework-$(VERSION)
	(cd roundcube-framework-$(VERSION); php /tmp/phpDocumentor.phar -d . -t ./doc --title="Roundcube Framework" --defaultpackagename="Framework" --template="clean")
	(cd roundcube-framework-$(VERSION); rm -rf doc/phpdoc-cache* .phpdoc)
	tar czf roundcube-framework-$(VERSION).tar.gz roundcube-framework-$(VERSION)
	rm -rf roundcube-framework-$(VERSION)

sign:
	gpg -u $(GPGKEY) -a --detach-sig roundcubemail-$(VERSION).tar.gz
	gpg -u $(GPGKEY) -a --detach-sig roundcubemail-$(VERSION)-complete.tar.gz
	gpg -u $(GPGKEY) -a --detach-sig roundcube-framework-$(VERSION).tar.gz

verify:
	gpg -v --verify roundcubemail-$(VERSION).tar.gz{.asc,}
	gpg -v --verify roundcubemail-$(VERSION)-complete.tar.gz{.asc,}
	gpg -v --verify roundcube-framework-$(VERSION).tar.gz{.asc,}

shasum:
	shasum -a 256 roundcubemail-$(VERSION).tar.gz roundcubemail-$(VERSION)-complete.tar.gz roundcube-framework-$(VERSION).tar.gz

roundcubemail-git: buildtools
	git clone $(GITREMOTE) roundcubemail-git
	(cd roundcubemail-git; git checkout $(GITBRANCH))
	(cd roundcubemail-git; bin/jsshrink.sh; bin/updatecss.sh; bin/cssshrink.sh)
	(cd roundcubemail-git/skins/elastic; \
		lessc --clean-css="--s1 --advanced" styles/styles.less > styles/styles.min.css; \
		lessc --clean-css="--s1 --advanced" styles/print.less > styles/print.min.css; \
		lessc --clean-css="--s1 --advanced" styles/embed.less > styles/embed.min.css)
	(cd roundcubemail-git/bin; rm -f transifexpull.sh package2composer.sh)
	(cd roundcubemail-git; find . -name '.gitignore' | xargs rm)
	(cd roundcubemail-git; find . -name '.travis.yml' | xargs rm)
	(cd roundcubemail-git; rm -rf tests plugins/*/tests .git* .tx* .ci* .editorconfig* index-test.php Dockerfile Makefile)
	(cd roundcubemail-git; sed -i '' 's/1.5-git/$(VERSION)/' index.php public_html/index.php installer/index.php program/include/iniset.php program/lib/Roundcube/bootstrap.php)
	(cd roundcubemail-git; sed -i '' 's/# Unreleased/# Release $(VERSION)'/ CHANGELOG.md)

buildtools: /tmp/composer.phar
	npm install -g uglify-js
	npm install -g lessc
	npm install -g less-plugin-clean-css
	npm install -g csso-cli
	which -s jq || echo "!!!!!! Please install jq (https://stedolan.github.io/jq/) !!!!!!"

/tmp/composer.phar:
	curl -sS https://getcomposer.org/installer | php -- --install-dir=/tmp/

/tmp/phpDocumentor.phar:
	curl -sSL https://phpdoc.org/phpDocumentor.phar -o /tmp/phpDocumentor.phar

clean:
	rm -rf roundcubemail-git
	rm -rf roundcubemail-$(VERSION)*
