FROM php:7.4-cli

ENV USER_HOME=/srv/feed-io-api
ENV APP_HOME=${USER_HOME}/app
ENV APP_ENV=${APP_ENV}

ENV DOCKER_UID=1000
ENV DOCKER_GID=1000

RUN groupadd -g ${DOCKER_GID} feed-io &&\
 useradd -d ${USER_HOME} -u ${DOCKER_UID} -g ${DOCKER_GID} feed-io &&\
 mkdir ${USER_HOME} &&\
 chown -R feed-io:feed-io ${USER_HOME}

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends unzip libzip-dev zlib1g-dev &&\
 feed-io-php-ext-install -j 8 zip &&\
 pecl install mongodb &&\
 feed-io-php-ext-enable mongodb &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER feed-io
WORKDIR ${APP_HOME}
