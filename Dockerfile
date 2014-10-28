FROM debian:latest
MAINTAINER Alex Brandt <alunduil@alunduil.com>

EXPOSE 80 443

RUN apt-get -qq update
RUN apt-get install -qq apache2-mpm-event

RUN sed -e 's|/var/www|&/public_html|' -e 's/\(Log \+\)[^ ]\+/\1"|cat"/' -i /etc/apache2/sites-available/default 

RUN a2enmod expires
RUN a2enmod headers

RUN apt-get install -qq php5 php-pear php5-mysql php5-pgsql php5-sqlite
RUN pear install mail_mime mail_mimedecode net_smtp2-beta net_idna2-beta auth_sasl2-beta net_sieve crypt_gpg

RUN rm -rf /var/www
ADD . /var/www

RUN echo -e '<?php\n$config = array();\n' > /var/www/config/config.inc.php
RUN rm -rf /var/www/installer

RUN . /etc/apache2/envvars && chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} /var/www/temp /var/www/logs

ENTRYPOINT [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
CMD [ "-k", "start" ]
