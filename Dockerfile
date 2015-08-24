FROM debian:jessie
MAINTAINER Alex Brandt <alunduil@alunduil.com>

EXPOSE 80 443

ENV DEBIAN_FRONTEND noninteractive

# Install Requirements
RUN apt-get update && \
    apt-get install -y apache2-mpm-event ca-certificates && \
    apt-get install -y php5 php-pear php5-mysql php5-pgsql php5-sqlite && \
    # Install Pear Requirements
    pear install mail_mime mail_mimedecode net_smtp net_idna2-beta auth_sasl net_sieve crypt_gpg && \
    # Cleanup
    rm -rf /var/lib/apt/lists/*

# Host Configuration
RUN sed -e 's|/var/www/html|/var/www/public_html|' -e 's@\(Log \+\)[^ ]\+@\1"|/bin/cat"@' -i /etc/apache2/sites-available/000-default.conf && \
    a2ensite 000-default && \
    sed -e 's|/var/www/html|/var/www/public_html|' -e 's@\(Log \+\)[^ ]\+@\1"|/bin/cat"@' -i /etc/apache2/sites-available/default-ssl.conf && \
    sed -e '/SSLCertificateKeyFile/s|ssl-cert-snakeoil.key|ssl-cert.key|' -e '/SSLCertificateFile/s|ssl-cert-snakeoil.pem|ssl-cert.pem|' -i /etc/apache2/sites-available/default-ssl.conf && \
    ln -snf ssl-cert-snakeoil.pem /etc/ssl/certs/ssl-cert.pem && \
    ln -snf ssl-cert-snakeoil.key /etc/ssl/private/ssl-cert.key && \
    a2ensite default-ssl && \
    a2enmod expires && \
    a2enmod headers && \
    a2enmod ssl && \
    rm -rf /var/www/*

# Add Code
ADD . /var/www

# App Configuration
RUN echo '<?php\n$config = array();\n' > /var/www/config/config.inc.php && \
    . /etc/apache2/envvars && chown -R ${APACHE_RUN_USER}:${APACHE_RUN_GROUP} /var/www/temp /var/www/logs

CMD [ "/usr/sbin/apache2ctl", "-D", "FOREGROUND", "-k", "start" ]
