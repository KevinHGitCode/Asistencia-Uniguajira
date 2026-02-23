#!/bin/sh
set -e

echo "📦 Iniciando contenedor Laravel..."

# Crear las carpetas necesarias de Laravel y asignar permisos
mkdir -p storage/framework/{sessions,views,cache} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

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
