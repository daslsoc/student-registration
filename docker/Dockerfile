FROM php:8.2-fpm

# Install needed extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo_mysql zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy config for Xdebug coverage if needed:
# Alternatively, you can just add to php.ini or write your own xdebug.ini
RUN echo "zend_extension=xdebug.so\n\
xdebug.mode=coverage\n\
xdebug.start_with_request=yes\n\
" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copy custom FPM config
COPY docker/fpm-pool.conf /usr/local/etc/php-fpm.d/zzz-pool.conf

# Install Composer globally (matches the container's PHP 8.2 runtime so
# `make install` / `make test` resolve dependencies against the real platform).
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Copy composer files, then install
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
