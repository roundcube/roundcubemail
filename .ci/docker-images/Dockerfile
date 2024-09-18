ARG PHPVERSION=8.3
FROM php:${PHPVERSION}-cli

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
 && apt-get clean

# Some tests require en_US.UTF-8 as locale.
RUN sed -i 's/^# en_US.UTF-8 /en_US.UTF-8 /' /etc/locale.gen && locale-gen

# TODO: Do we need the multiarch-args? What for?
#RUN debMultiarch="$(dpkg-architecture --query DEB_BUILD_MULTIARCH)" \
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
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

# Create working directory
WORKDIR /app

VOLUME /app
