FROM php:7.4-cli

ENV USER_HOME=/srv/feed-io-api
ENV APP_HOME=${USER_HOME}/app
ENV APP_ENV=${APP_ENV}

ENV DOCKER_UID=1000
ENV DOCKER_GID=1000

RUN groupadd -g ${DOCKER_GID} docker &&\
 useradd -d ${USER_HOME} -u ${DOCKER_UID} -g ${DOCKER_GID} docker &&\
 mkdir ${USER_HOME} &&\
 chown -R docker:docker ${USER_HOME}

RUN set -ex &&\
 apt-get update &&\
 apt-get install -y --no-install-recommends unzip libjpeg-dev libfreetype6-dev libzip-dev zlib1g-dev libpng-dev &&\
 docker-php-ext-configure gd --with-freetype --with-jpeg &&\
 docker-php-ext-install -j 8 zip gd &&\
 pecl install mongodb &&\
 docker-php-ext-enable mongodb &&\
 rm -rf /var/lib/apt/lists/* && rm -rf /tmp/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

USER docker
WORKDIR ${APP_HOME}
