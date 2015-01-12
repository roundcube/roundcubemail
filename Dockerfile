FROM debian:latest
MAINTAINER Alex Brandt <alunduil@alunduil.com>

EXPOSE 80 443

RUN apt-get -qq update
RUN apt-get install -qq apache2-mpm-event

RUN sed -e 's|/var/www|&/public_html|' -e 's/\(Log \+\)[^ ]\+/\1"|cat"/' -i /etc/apache2/sites-available/default 
RUN a2ensite default

RUN sed -e 's|/var/www|&/public_html|' -e 's/\(Log \+\)[^ ]\+/\1"|cat"/' -i /etc/apache2/sites-available/default-ssl
RUN sed -e '/SSLCertificateKeyFile/s|ssl-cert-snakeoil.key|ssl-cert.key|' -e '/SSLCertificateFile/s|ssl-cert-snakeoil.pem|ssl-cert.pem|' -i /etc/apache2/sites-available/default-ssl
RUN ln -snf ssl-cert-snakeoil.pem /etc/ssl/certs/ssl-cert.pem
RUN ln -snf ssl-cert-snakeoil.key /etc/ssl/private/ssl-cert.key
RUN a2ensite default-ssl

RUN a2enmod expires
RUN a2enmod headers
RUN a2enmod ssl

RUN apt-get install -qq php5 php-pear php5-mysql php5-pgsql php5-sqlite
RUN pear install mail_mime mail_mimedecode net_smtp net_idna2-beta auth_sasl net_sieve crypt_gpg

RUN rm -rf /var/www
ADD . /var/www

RUN echo -e '<?php\n$config = array();\n' > /var/www/config/config.inc.php
RUN rm -rf /var/www/installer

RUN . /etc/apache2/envvars && chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} /var/www/temp /var/www/logs

ENTRYPOINT [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND" ]
CMD [ "-k", "start" ]
