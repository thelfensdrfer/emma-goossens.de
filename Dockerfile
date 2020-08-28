FROM php:7.4-fpm

MAINTAINER Tim Helfensdörfer <thelfensdrfer@gmail.com>

# Install extensions
RUN apt-get update && apt-get install \
    libpng-dev \
    libzip-dev zip \
    libmagickwand-dev \
    ffmpeg \
    libcurl4-openssl-dev pkg-config libssl-dev -y
RUN docker-php-ext-install -j$(nproc) zip curl gd exif
RUN pecl install imagick redis
RUN docker-php-ext-enable imagick redis

COPY ./docker/php/shell-memory-limit.ini /usr/local/etc/php/conf.d/memory-limit-php.ini

ADD ./src /app

WORKDIR /app

CMD ["php-fpm"]
