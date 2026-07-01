FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

COPY . .
RUN composer dump-autoload --optimize \
    && php artisan package:discover --ansi

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM php:8.3-fpm-bookworm AS production

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        ca-certificates \
        git \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && pecl channel-update pecl.php.net \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

COPY --from=vendor /app /var/www
COPY --from=assets /app/public/build /var/www/public/build
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p \
        /var/www/storage/app/public \
        /var/www/storage/framework/cache \
        /var/www/storage/framework/sessions \
        /var/www/storage/framework/views \
        /var/www/storage/logs \
        /var/www/bootstrap/cache \
        /var/www/public-shared \
    && chown -R www-data:www-data /var/www

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
