FROM php:7.4-fpm

RUN chown -R www-data: /var/www/html

ARG DOCKER_UID=1000
ENV WWW_DATA_UID=${DOCKER_UID}
ENV APP_ENV=${APP_ENV}

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends libicu-dev &&\
 docker-php-ext-install -j 8 intl &&\
 pecl install mongodb redis &&\
 docker-php-ext-configure intl &&\
 docker-php-ext-enable mongodb redis &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

USER www-data
WORKDIR /var/www/html
