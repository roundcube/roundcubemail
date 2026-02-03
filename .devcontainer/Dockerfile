FROM php:8.4-cli-bookworm

ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    # Build tools required for PHP extension configuration
    autoconf \
    # PHP extension dependencies
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libldap2-dev \
    libpng-dev \
    libpq-dev \
    libsqlite3-dev \
    libzip-dev \
    libonig-dev \
    libldap-common \
    libenchant-2-dev \
    # ImageMagick for Imagick extension
    libmagickwand-dev \
    # Tools
    nodejs \
    npm \
    aspell \
    aspell-en \
    hunspell-en-us \
    git \
    curl \
    unzip \
    locales \
    sudo \
    # For health checks and debugging
    netcat-openbsd \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install MariaDB client from Debian repositories
RUN apt-get update \
    && apt-get install -y --no-install-recommends mariadb-client \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Set up locales
RUN sed -i 's/^# en_US.UTF-8 /en_US.UTF-8 /' /etc/locale.gen && locale-gen

# Install PHP extensions
# Note: php-imap is NOT required - Roundcube has its own IMAP implementation using sockets
# Note: pspell is optional - Roundcube can use enchant or web-based spellcheck (googie) instead
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-configure ldap \
    && docker-php-ext-install \
        zip \
        pcntl \
        gd \
        ldap \
        intl \
        enchant \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mysqli \
        opcache \
        exif

# Install Imagick via PECL (for image manipulation)
RUN pecl install imagick && docker-php-ext-enable imagick

# Install Xdebug for debugging
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Configure Xdebug
RUN echo "xdebug.mode=debug,coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create non-root user
ARG USERNAME=vscode
ARG USER_UID=1000
ARG USER_GID=$USER_UID

RUN groupadd --gid $USER_GID $USERNAME \
    && useradd --uid $USER_UID --gid $USER_GID -m $USERNAME \
    && echo $USERNAME ALL=\(root\) NOPASSWD:ALL > /etc/sudoers.d/$USERNAME \
    && chmod 0440 /etc/sudoers.d/$USERNAME

# Set working directory
WORKDIR /workspace

USER $USERNAME
