#!/bin/bash
set -ex

# PWD=`pwd`

if [[ "$1" == apache2* ]] || [ "$1" == php-fpm ]; then
  if ! [ -e index.php -a -e bin/installto.sh ]; then
    echo >&2 "roundcubemail not found in $PWD - copying now..."
    if [ "$(ls -A)" ]; then
      echo >&2 "WARNING: $PWD is not empty - press Ctrl+C now if this is an error!"
      ( set -x; ls -A; sleep 10 )
    fi
    tar cf - --one-file-system -C /usr/src/roundcubemail . | tar xf -
    sed -i 's/mod_php5.c/mod_php7.c/' .htaccess
    echo >&2 "Complete! ROUNDCUBEMAIL has been successfully copied to $PWD"
  fi

  if [ ! -z "${!MYSQL_ENV_MYSQL_*}" ]; then
    : "${ROUNDCUBEMAIL_DB_TYPE:=mysql}"
    : "${ROUNDCUBEMAIL_DB_HOST:=${MYSQL_ENV_MYSQL_HOST:-mysql}}"
    : "${ROUNDCUBEMAIL_DB_USER:=${MYSQL_ENV_MYSQL_USER:-root}}"
    if [ "$ROUNDCUBEMAIL_DB_USER" = 'root' ]; then
      : "${ROUNDCUBEMAIL_DB_PASSWORD:=${MYSQL_ENV_MYSQL_ROOT_PASSWORD}}"
    else
      : "${ROUNDCUBEMAIL_DB_PASSWORD:=${MYSQL_ENV_MYSQL_PASSWORD}}"
    fi
    : "${ROUNDCUBEMAIL_DB_NAME:=${MYSQL_ENV_MYSQL_DATABASE:-roundcubemail}}"
    : "${ROUNDCUBEMAIL_DSNW:=${ROUNDCUBEMAIL_DB_TYPE}://${ROUNDCUBEMAIL_DB_USER}:${ROUNDCUBEMAIL_DB_PASSWORD}@${ROUNDCUBEMAIL_DB_HOST}/${ROUNDCUBEMAIL_DB_NAME}}"
  else
    # use local SQLite DB in /var/www/html/db
    : "${ROUNDCUBEMAIL_DB_DIR:=$PWD/db}"
    : "${ROUNDCUBEMAIL_DSNW:=sqlite:///$ROUNDCUBEMAIL_DB_DIR/sqlite.db?mode=0646}"

    mkdir -p $ROUNDCUBEMAIL_DB_DIR
    chown www-data:www-data $ROUNDCUBEMAIL_DB_DIR
  fi

  : "${ROUNDCUBEMAIL_DEFAULT_HOST:=localhost}"
  : "${ROUNDCUBEMAIL_DEFAULT_PORT:=143}"
  : "${ROUNDCUBEMAIL_SMTP_SERVER:=localhost}"
  : "${ROUNDCUBEMAIL_SMTP_PORT:=587}"
  : "${ROUNDCUBEMAIL_PLUGINS:=archive,zipdownload}"
  : "${ROUNDCUBEMAIL_TEMP_DIR:=/tmp/roundcube-temp}"
  : "${ROUNDCUBEMAIL_LOG_DIR:=/var/log/roundcubemail}"

  if [ ! -e config/config.inc.php ]; then
    ROUNDCUBEMAIL_PLUGINS_PHP=`echo "${ROUNDCUBEMAIL_PLUGINS}" | sed -E "s/[, ]+/', '/"`
    mkdir -p ${ROUNDCUBEMAIL_TEMP_DIR} && chown www-data ${ROUNDCUBEMAIL_TEMP_DIR}
    mkdir -p ${ROUNDCUBEMAIL_LOG_DIR} && chown www-data ${ROUNDCUBEMAIL_LOG_DIR}
    touch config/config.inc.php

    echo "Write config to $PWD/config/config.inc.php"
    echo "<?php
    \$config['db_dsnw'] = '${ROUNDCUBEMAIL_DSNW}';
    \$config['db_dsnr'] = '${ROUNDCUBEMAIL_DSNR}';
    \$config['default_host'] = '${ROUNDCUBEMAIL_DEFAULT_HOST}';
    \$config['default_port'] = '${ROUNDCUBEMAIL_DEFAULT_PORT}';
    \$config['smtp_server'] = '${ROUNDCUBEMAIL_SMTP_SERVER}';
    \$config['smtp_port'] = '${ROUNDCUBEMAIL_SMTP_PORT}';
    \$config['log_dir'] = '${ROUNDCUBEMAIL_LOG_DIR}';
    \$config['temp_dir'] = '${ROUNDCUBEMAIL_TEMP_DIR}';
    \$config['plugins'] = ['${ROUNDCUBEMAIL_PLUGINS_PHP}'];
    ?>" | tee config/config.inc.php

    # initialize DB if not SQLite
    echo "${ROUNDCUBEMAIL_DSNW}" | grep -q 'sqlite:' || bin/initdb.sh --dir=$PWD/SQL || bin/updatedb.sh --dir=$PWD/SQL --package=roundcube
  else
    echo "WARNING: $PWD/config/config.inc.php already exists."
    echo "ROUNDCUBEMAIL_* environment variables have been ignored."
  fi
fi

exec "$@"