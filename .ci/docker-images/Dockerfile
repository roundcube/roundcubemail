ARG PHPVERSION=8.3
FROM php:${PHPVERSION}-cli
LABEL org.opencontainers.image.source="https://github.com/roundcube/roundcubemail"

# To use it below we must repeat the ARG declaration
ARG PHPVERSION

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
		libfreetype6-dev \
		libicu-dev \
		libjpeg62-turbo-dev \
		libldap2-dev \
		libpng-dev \
		libpq-dev \
		libsqlite3-dev \
		libzip-dev \
		libpspell-dev \
		libonig-dev \
		libldap-common \
		libenchant-2-dev \
		nodejs \
		npm \
		aspell \
		aspell-en \
		aspell-de \
		hunspell-en-us \
		git \
		locales \
		rsync \
    && apt-get clean

# Some tests require en_US.UTF-8 as locale.
RUN sed -i 's/^# en_US.UTF-8 /en_US.UTF-8 /' /etc/locale.gen && locale-gen

# TODO: Do we need the multiarch-args? What for?
#RUN debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)" \
# For PHP >= v8.4 pspell was moved to PECL, so we have to fetch and unpack it first. (We don't use `pecl install` just because it makes the rest of the script simpler.)
RUN if php -r 'exit(version_compare(getenv("PHPVERSION"), "8.4", ">=") ? 0 : 1);'; then pecl bundle -d /usr/src/php/ext pspell; fi \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 #&& docker-php-ext-configure ldap --with-libdir="lib/$debMultiarch" \
 && docker-php-ext-configure ldap \
 && docker-php-ext-install \
	zip \
	pcntl \
	gd \
	ldap \
	intl \
	pspell \
	enchant

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY --chmod=755 ./.ci/docker-images/run-in-copy.sh /usr/local/bin/run-in-copy.sh

# Create working directory
WORKDIR /app

VOLUME /app
