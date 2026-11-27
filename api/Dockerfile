# syntax=docker/dockerfile:1

# PHP-FPM + nginx dans un seul conteneur, écoute sur 8080 (HTTP : le TLS est terminé par le reverse proxy).
FROM serversideup/php:8.4-fpm-nginx

ENV PHP_OPCACHE_ENABLE=1 \
    SSL_MODE=off \
    AUTORUN_ENABLED=true \
    AUTORUN_LARAVEL_STORAGE_LINK=false \
    AUTORUN_LARAVEL_MIGRATION=true \
    AUTORUN_LARAVEL_MIGRATION_ISOLATION=true

# Les migrations sont appliquées au démarrage du conteneur (ADR 0003).
WORKDIR /var/www/html
COPY --chown=www-data:www-data composer.json composer.lock ./
USER www-data
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist
COPY --chown=www-data:www-data . .
RUN composer dump-autoload --no-dev --optimize --no-interaction

EXPOSE 8080
