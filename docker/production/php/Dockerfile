# Stage 1: Build Frontend Assets
FROM node:22-alpine AS frontend_assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./

RUN npm run build

# Stage 2: Cache dependencies
FROM composer:2 AS composer_deps

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --ignore-platform-reqs \
    --no-scripts

# Stage 3: Production PHP-FPM
FROM php:8.2-fpm-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    oniguruma-dev \
    curl-dev \
    git \
    unzip \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        pgsql \
        zip

# Install Redis extension
RUN apk add --no-cache pcre-dev ${PHPIZE_DEPS} \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev ${PHPIZE_DEPS}

WORKDIR /var/www/html

# Copy codebase
COPY . .

# Copy vendored dependencies
COPY --from=composer_deps /app/vendor ./vendor

# Copy built frontend assets so Laravel Vite helper knows the correct hashes
COPY --from=frontend_assets /app/public/build ./public/build

# Provide custom PHP config
COPY docker/production/php/custom.ini /usr/local/etc/php/conf.d/custom.ini

# Ensure cache directories exist and fix permissions
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Production cache optimization
RUN php artisan route:cache \
    && php artisan view:cache \
    && php artisan event:cache

EXPOSE 9000

CMD ["php-fpm"]
