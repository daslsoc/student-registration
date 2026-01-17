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

WORKDIR /var/www/html

# Copy Laravel source
COPY . .

# Copy composer files, then install
COPY composer.json composer.lock ./
RUN curl -sS https://getcomposer.org/installer | php && \
    php composer.phar install --no-dev --optimize-autoloader

# Set correct permissions
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
