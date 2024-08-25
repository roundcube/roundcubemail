# Use the official PHP image as the base
FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libldap2-dev \
    libzip-dev \
    libmagickwand-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip intl ldap

# Install Imagick
RUN pecl install imagick && docker-php-ext-enable imagick

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js and npm (latest stable version)
RUN curl -sL https://deb.nodesource.com/setup_current.x | bash - \
    && apt-get install -y nodejs

# Set the working directory
WORKDIR /var/www/html

# Debug: List files in the working directory
RUN ls -la /var/www/html

# Copy the entrypoint script
COPY entrypoint.sh /usr/local/bin/

# Ensure the entrypoint script is executable
RUN chmod +x /usr/local/bin/entrypoint.sh

# Copy the rest of the application code
COPY . .

# Set the timezone in php.ini
RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/timezone.ini

# Expose the port the app runs on
EXPOSE 80

# Set the entrypoint
ENTRYPOINT ["entrypoint.sh"]

# Start Apache server
CMD ["apache2-foreground"]
