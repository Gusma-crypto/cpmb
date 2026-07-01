#!/bin/sh
set -e

mkdir -p \
    /var/www/storage/app/public \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/sessions \
    /var/www/storage/framework/views \
    /var/www/storage/logs \
    /var/www/bootstrap/cache \
    /var/www/public-shared

chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache \
    /var/www/public-shared

if [ ! -f /var/www/public-shared/.copied ]; then
    echo "Copying public assets to shared Nginx volume..."
    rm -rf /var/www/public-shared/*
    cp -a /var/www/public/. /var/www/public-shared/
    touch /var/www/public-shared/.copied
fi

if [ -f /var/www/.env ]; then
    php artisan storage:link >/dev/null 2>&1 || true
    php artisan config:cache >/dev/null 2>&1 || true
    php artisan route:cache >/dev/null 2>&1 || true
    php artisan view:cache >/dev/null 2>&1 || true

    if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
        php artisan migrate --force
    fi
fi

exec "$@"
