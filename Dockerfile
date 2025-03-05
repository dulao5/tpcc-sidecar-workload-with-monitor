FROM php:7.4-fpm-alpine

ENV WORKDIR=/var/www/html
WORKDIR $WORKDIR

RUN set -x \
    && apk add --no-cache zlib-dev \
    && docker-php-ext-install pdo_mysql

COPY ./src .
COPY php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf
