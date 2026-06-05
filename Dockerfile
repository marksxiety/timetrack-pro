FROM composer:2 AS composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-scripts --ignore-platform-req=php


FROM node:20-alpine AS node

WORKDIR /var/www/html

COPY package*.json ./
RUN npm install

COPY . .
COPY --from=composer /var/www/html/vendor vendor
RUN npm run build


FROM php:8.2-cli-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip gd intl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .
COPY --from=composer /var/www/html/vendor vendor
COPY --from=node /var/www/html/public/build public/build

RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

RUN mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
