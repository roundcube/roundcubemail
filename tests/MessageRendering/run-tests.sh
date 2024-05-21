#!/bin/bash

testsdir=$(realpath $(dirname $0))
cont_name="roundcubetest-dovecot"

docker run -it --rm -d --name "$cont_name" \
	-p 143:143 \
	-v "$testsdir/src/maildir:/srv/mail/test" \
	-v "$testsdir/dovecot-maildir.conf:/etc/dovecot/conf.d/dovecot-maildir.conf" \
	docker.io/dovecot/dovecot:latest >/dev/null || exit 1

"$testsdir/../../vendor/bin/phpunit" -c "$testsdir/phpunit.xml" --fail-on-warning --fail-on-risky

docker stop "$cont_name" >/dev/null
