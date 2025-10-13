#!/bin/sh
set -e

echo "📦 Iniciando contenedor Laravel..."

# Crear las carpetas necesarias de Laravel
mkdir -p \
  storage/framework/sessions \
  storage/framework/views \
  storage/framework/cache \
  bootstrap/cache

# Asignar permisos correctos
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Limpiar y regenerar cachés (sin romper el arranque si algo falla)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true
php artisan cache:clear || true

# Regenerar symlink de storage si no existe
if [ ! -L "public/storage" ]; then
  php artisan storage:link --relative || true
fi

# Iniciar PHP-FPM y Nginx
php-fpm -D
nginx -g "daemon off;"
