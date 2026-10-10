FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
        postgresql-dev \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
    && docker-php-ext-install pdo_pgsql mbstring intl zip bcmath opcache \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
