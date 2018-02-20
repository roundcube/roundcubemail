FROM php:7.1-apache
MAINTAINER Thomas Bruederli <thomas@roundcube.net>

RUN apt-get -qq update \
  && apt-get install -qq \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libldap2-dev \
    libmcrypt-dev \
    libpng-dev \
    libpq-dev \
    libsqlite3-dev \
    zlib1g-dev \
  && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install -j$(nproc) exif intl pdo pdo_mysql pdo_pgsql pdo_sqlite zip
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ && docker-php-ext-install -j$(nproc) gd
RUN docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu && docker-php-ext-install -j$(nproc) ldap
RUN docker-php-ext-configure mcrypt --with-mcrypt && docker-php-ext-install -j$(nproc) mcrypt

# enable mod_rewrite
RUN a2enmod rewrite

# expose these volumes
VOLUME /var/roundcube/config
VOLUME /tmp/roundcube-temp

# Define Roundcubemail version
ENV ROUNDCUBEMAIL_VERSION 1.3.4

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

# include the wait-for-it.sh script
RUN curl https://raw.githubusercontent.com/vishnubob/wait-for-it/master/wait-for-it.sh > /wait-for-it.sh && chmod +x /wait-for-it.sh

COPY docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
