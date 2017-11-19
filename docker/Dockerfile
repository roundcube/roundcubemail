FROM php:7.1-apache
MAINTAINER Thomas Bruederli <thomas@roundcube.net>

RUN apt-get -qq update \
  && apt-get install -qq \
    libpq-dev \
    libsqlite3-dev \
    libicu-dev \
    zlib1g-dev \
    libpng-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
  && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install -j$(nproc) intl exif pdo pdo_mysql pdo_sqlite pdo_pgsql zip
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install -j$(nproc) gd

# enable mod_rewrite
RUN a2enmod rewrite

VOLUME /var/www/html

# Define Roundcubemail version
ENV ROUNDCUBEMAIL_VERSION 1.3.2

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
  && rm -rf /usr/src/roundcubemail/installer \
&& rm roundcubemail.tar.gz

COPY docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

