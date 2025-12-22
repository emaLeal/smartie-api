# Stage 1: Build
FROM composer:2.9 AS builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
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
    postgresql-dev

# --- SECCIÓN NUEVA: INSTALACIÓN DE PHPREDIS ---
# Usamos $PHPIZE_DEPS para incluir autoconf, gcc, make, etc., necesarios para PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps
# ----------------------------------------------

RUN docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring gd

WORKDIR /app

COPY --from=builder /app .

RUN chown -R www-data:www-data /app

USER www-data

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
