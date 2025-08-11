# Etapa 1: Construcción (Composer + Node + PHP)
FROM php:8.2-fpm as build

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    libonig-dev libxml2-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd bcmath opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Etapa 2: Producción (PHP-FPM + Nginx)
FROM php:8.2-fpm-alpine as production

RUN apk add --no-cache nginx bash

RUN mkdir -p /run/nginx

# Copiar configuración de Nginx en la ubicación correcta
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

COPY --from=build /var/www/html /var/www/html

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD sh -c "php artisan migrate --force && php-fpm -D && nginx -g 'daemon off;'"
