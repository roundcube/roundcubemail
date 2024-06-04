#!/bin/bash

testsdir=$(realpath $(dirname $0))
cont_name="roundcubetest-dovecot"

ROUNDCUBE_TEST_IMAP_SERVER_IMAGE="docker.io/dovecot/dovecot:latest"

test -f "$testsdir/.env" && source "$testsdir/.env"

docker run -it --rm -d --name "$cont_name" \
	-p 143:143 \
	-v "$testsdir/data/maildir:/srv/mail/test" \
	-v "$testsdir/dovecot-maildir.conf:/etc/dovecot/conf.d/dovecot-maildir.conf" \
	"$ROUNDCUBE_TEST_IMAP_SERVER_IMAGE" >/dev/null || exit 1

"$testsdir/../../vendor/bin/phpunit" -c "$testsdir/phpunit.xml" --fail-on-warning --fail-on-risky

docker stop "$cont_name" >/dev/null
