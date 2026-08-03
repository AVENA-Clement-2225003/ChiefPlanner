FROM php:8.2-apache

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates gnupg \
        libpng-dev libzip-dev libonig-dev \
        libxml2-dev libcurl4-openssl-dev \
 && docker-php-ext-install pdo_mysql mbstring zip opcache \
 && a2enmod rewrite headers \
 && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf \
 && printf '<Directory /var/www/html/public>\n    Options -Indexes +FollowSymLinks\n    AllowOverride All\n    Require all granted\n</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN { \
    echo 'memory_limit=512M'; \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=256'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.revalidate_freq=60'; \
} > /usr/local/etc/php/conf.d/99-laravel.ini

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get update \
 && apt-get install -y --no-install-recommends nodejs \
 && apt-get clean \
 && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

WORKDIR /var/www/html
COPY . .
RUN rm -f .env

RUN composer install --no-dev --no-interaction --optimize-autoloader \
 && npm ci \
 && npm run build \
 && chown -R www-data:www-data storage bootstrap/cache \
 && chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
