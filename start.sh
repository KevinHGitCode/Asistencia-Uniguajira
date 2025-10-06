#!/bin/sh
set -e

# limpiar caches generados en build (asegura que lea variables de entorno de Render)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# habilitar storage link
rm -rf public/storage
php artisan storage:link

# Opcional: si quieres generar cache en runtime (después de asegurarte que env vars OK)
# php artisan config:cache --no-interaction || true

# iniciar servicios
php-fpm -D
nginx -g "daemon off;"
