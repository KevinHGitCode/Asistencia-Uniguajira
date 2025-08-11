#!/bin/sh
set -e

# Iniciar PHP-FPM y Nginx
php-fpm -D
nginx -g "daemon off;"
