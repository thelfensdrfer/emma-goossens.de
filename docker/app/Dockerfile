FROM php:8.0-fpm

MAINTAINER Tim Helfensdörfer <thelfensdrfer@gmail.com>

COPY ./docker/php/shell-memory-limit.ini /usr/local/etc/php/conf.d/memory-limit-php.ini

ADD ./src /app

WORKDIR /app

CMD ["php-fpm"]
