#!/usr/bin/env sh
set -e

# Espera conexión a DB antes de migrar (usa DB_HOST y DB_PORT de tus env vars)
DB_HOST=${DB_HOST:-${DATABASE_HOST}}
DB_PORT=${DB_PORT:-${DATABASE_PORT:-3306}}
MAX_WAIT=${DB_WAIT_SECONDS:-60}

echo "Waiting for DB ${DB_HOST}:${DB_PORT} (max ${MAX_WAIT}s)..."
i=0
while ! nc -z "$DB_HOST" "$DB_PORT" 2>/dev/null; do
  i=$((i+1))
  if [ "$i" -ge "$MAX_WAIT" ]; then
    echo "Timeout waiting for DB after ${MAX_WAIT}s"
    break
  fi
  sleep 1
done

# Intenta migrar y seedear; si falla, loguea y sigue (para evitar crash loop)
echo "Running migrations and seeders (if DB reachable)..."
if php artisan migrate --force --seed --no-interaction; then
  echo "Migrations + seeders ran successfully."
else
  echo "Migrations failed (may retry on next restart). Continuing startup..."
fi

# Arrancar PHP-FPM y Nginx
echo "Starting php-fpm and nginx..."
php-fpm -D
nginx -g 'daemon off;'
