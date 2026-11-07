FROM php:8.4-fpm

ARG HOST_UID=1000
ARG HOST_GID=1000

COPY --from=ghcr.io/mlocati/php-extension-installer:2.13.1 /usr/bin/install-php-extensions /usr/local/bin/

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions pdo_mysql intl zip opcache xdebug

RUN usermod -u ${HOST_UID} www-data && groupmod -g ${HOST_GID} www-data

COPY php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY xdebug.ini /usr/local/etc/php/conf.d/99-xdebug.ini

WORKDIR /var/www/html

USER www-data
