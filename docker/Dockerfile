FROM php:5.6-apache
MAINTAINER Alex Brandt <alunduil@alunduil.com>

RUN apt-get -qq update && apt-get install -qq libpq-dev libsqlite3-dev libicu-dev php-pear && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install -j$(nproc) intl exif pdo pdo_mysql pdo_sqlite pdo_pgsql
RUN pear update-channels && pear install mail_mime net_smtp net_idna2-beta auth_sasl net_sieve crypt_gpg && pear clear-cache

VOLUME /var/www/html

# Define Roundcubemail version
ENV ROUNDCUBEMAIL_VERSION 1.3.0

# Download package and extract to web volume
RUN curl -o roundcubemail.tar.gz -SL https://github.com/roundcube/roundcubemail/releases/download/${ROUNDCUBEMAIL_VERSION}/roundcubemail-${ROUNDCUBEMAIL_VERSION}-complete.tar.gz \
	&& curl -o roundcubemail.tar.gz.asc -SL https://github.com/roundcube/roundcubemail/releases/download/${ROUNDCUBEMAIL_VERSION}/roundcubemail-${ROUNDCUBEMAIL_VERSION}-complete.tar.gz.asc \
	&& export GNUPGHOME="$(mktemp -d)" \
	&& gpg --keyserver ha.pool.sks-keyservers.net --recv-keys F3E4C04BB3DB5D4215C45F7F5AB2BAA141C4F7D5 \
	&& gpg --batch --verify roundcubemail.tar.gz.asc roundcubemail.tar.gz \
	&& rm -r "$GNUPGHOME" roundcubemail.tar.gz.asc \
	&& tar -xzf roundcubemail.tar.gz -C /usr/src/ \
	# upstream tarballs include ./roundcubemail-${ROUNDCUBEMAIL_VERSION}/ so this gives us /usr/src/roundcubemail-${ROUNDCUBEMAIL_VERSION}
	&& mv /usr/src/roundcubemail-${ROUNDCUBEMAIL_VERSION} /usr/src/roundcubemail \
	&& chown -R www-data:www-data /usr/src/roundcubemail \
	&& rm roundcubemail.tar.gz

COPY docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
