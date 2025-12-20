# Stage 1: Build
FROM composer:2.9 AS builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
#    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

COPY . .

# Stage 2: Runtime
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    bash \
    libpng \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
&& docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring gd

WORKDIR /app

COPY --from=builder /app .

RUN chown -R www-data:www-data /app

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
