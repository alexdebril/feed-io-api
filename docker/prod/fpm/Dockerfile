FROM php:8.0-fpm

RUN chown -R www-data: /var/www/html

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends unzip libzip-dev zlib1g-dev libicu-dev &&\
 docker-php-ext-install -j 8 zip opcache intl &&\
 docker-php-ext-configure intl &&\
 pecl install mongodb redis &&\
 docker-php-ext-enable mongodb redis &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

COPY docker/prod/fpm/php.ini /usr/local/etc/php/

COPY . /var/www/html
WORKDIR /var/www/html

RUN set -ex &&\
 curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer &&\
 COMPOSER_ALLOW_SUPERUSER=1 APP_ENV=prod composer install --no-dev --classmap-authoritative --no-progress --no-suggest &&\
 rm -r /root/.composer
