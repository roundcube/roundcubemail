#!/bin/bash
set -ex

if [[ "$1" == apache2* ]] || [ "$1" == php-fpm ]; then
	if ! [ -e index.php -a -e bin/installto.sh ]; then
		echo >&2 "roundcubemail not found in $PWD - copying now..."
		if [ "$(ls -A)" ]; then
			echo >&2 "WARNING: $PWD is not empty - press Ctrl+C now if this is an error!"
			( set -x; ls -A; sleep 10 )
		fi
		tar cf - --one-file-system -C /usr/src/roundcubemail . | tar xf -
		echo >&2 "Complete! ROUNDCUBEMAIL has been successfully copied to $PWD"
	fi

	if [ -z "${!MYSQL_ENV_MYSQL_*}" ]; then
		: "${ROUNDCUBEMAIL_DB_TYPE:=mysql}"
		: "${ROUNDCUBEMAIL_DB_HOST:=mysql}"
		: "${ROUNDCUBEMAIL_DB_USER:=${MYSQL_ENV_MYSQL_USER:-root}}"
		if [ "$ROUNDCUBEMAIL_DB_USER" = 'root' ]; then
			: "${ROUNDCUBEMAIL_DB_PASSWORD:=${MYSQL_ENV_MYSQL_ROOT_PASSWORD}}"
		else
			: "${ROUNDCUBEMAIL_DB_PASSWORD:=${MYSQL_ENV_MYSQL_PASSWORD}}"
		fi
		: "${ROUNDCUBEMAIL_DB_NAME:=${MYSQL_ENV_MYSQL_DATABASE:roundcubemail}}"
	fi

	: "${ROUNDCUBEMAIL_DEFAULT_HOST:=localhost}"
	: "${ROUNDCUBEMAIL_SMTP_SERVER:=localhost}"
	: "${ROUNDCUBEMAIL_SMTP_PORT:=25}"

	if [ ! -e config/config.inc.php ]; then
		touch config/config.inc.php
		echo "Write config to $PWD/config/config.inc.php"
		echo "<?php
		\$config['db_dsnw'] = '${ROUNDCUBEMAIL_DB_TYPE}://${ROUNDCUBEMAIL_DB_USER}:${ROUNDCUBEMAIL_DB_PASSWORD}@${ROUNDCUBEMAIL_DB_HOST}/${ROUNDCUBEMAIL_DB_NAME}';
		\$config['default_host'] = '${ROUNDCUBEMAIL_DEFAULT_HOST}';
		\$config['smtp_server'] = '${ROUNDCUBEMAIL_SMTP_SERVER}';
		\$config['smtp_port'] = '${ROUNDCUBEMAIL_SMTP_PORT}';
		\$config['enable_installer'] = true;
		?>" | tee config/config.inc.php
	else
		echo "WARNING: $PWD/config/config.inc.php already exists."
		echo "roundcubemail related environment variables have been ignored."
	fi
fi

exec "$@"
