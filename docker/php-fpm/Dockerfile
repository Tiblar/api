FROM php:7.4-fpm

RUN apt-get update \
    && apt-get install -y zlib1g-dev libpq-dev g++ git libicu-dev zip \
      libmagickwand-dev libzip-dev zip sox libsox-fmt-all ffmpeg \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install gd

WORKDIR /var/www/api

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash

RUN mkdir -p var
RUN chown www-data:www-data -R var
RUN chmod -R ug+rwX var