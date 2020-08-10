FROM php:7.4-cli

ENV USER_HOME=/srv/feed-io-api
ENV APP_FOLDER=${USER_HOME}/app
ENV APP_ENV=${APP_ENV}

ARG DOCKER_UID=1000
ARG DOCKER_GID=1000

RUN mkdir -p /srv/feed-io-api/app && groupadd -g ${DOCKER_GID} feed-io &&\
 useradd -d ${USER_HOME} -u ${DOCKER_UID} -g ${DOCKER_GID} feed-io &&\
 chown -R feed-io:feed-io ${USER_HOME}

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends unzip libzip-dev zlib1g-dev &&\
 docker-php-ext-install -j 8 zip &&\
 pecl install mongodb &&\
 docker-php-ext-enable mongodb &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

USER feed-io

COPY ../../.. ${USER_HOME}/app
WORKDIR ${USER_HOME}/app

RUN set -ex &&\
 mkdir ${USER_HOME}/bin &&\
 curl -sS https://getcomposer.org/installer | php -- --install-dir=${USER_HOME}/bin --filename=composer &&\
 APP_ENV=prod ${USER_HOME}/bin/composer install --no-dev --classmap-authoritative --no-progress --no-suggest &&\
 rm -r ${USER_HOME}/.composer ${USER_HOME}/bin
