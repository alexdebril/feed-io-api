FROM php:8.0-fpm

RUN chown -R www-data: /var/www/html

ENV DOCKER_UID=1000
ENV DOCKER_GID=1000

ENV WWW_DATA_UID=${DOCKER_UID}
ENV APP_ENV=${APP_ENV}

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends unzip libzip-dev zlib1g-dev &&\
 docker-php-ext-install -j 8 opcache &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g ${DOCKER_GID} feed-io && usermod -u ${DOCKER_UID} www-data && usermod -g ${DOCKER_GID} www-data

RUN mkdir /var/www/.composer && chown -R www-data:www-data /var/www/.composer

USER www-data
WORKDIR /var/www/html
