# Etapa 1: Construcción (Composer + Node + PHP)
FROM php:8.2-fpm as build

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    libonig-dev libxml2-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd bcmath opcache

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Configuración de trabajo
WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP y JS
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

# Cachear configuración de Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Etapa 2: Producción con Nginx
FROM nginx:alpine

# Copiar configuración de Nginx adaptada a Laravel
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Copiar PHP-FPM y proyecto desde la etapa build
COPY --from=build /usr/local/etc/php /usr/local/etc/php
COPY --from=build /usr/local/bin /usr/local/bin
COPY --from=build /usr/local/lib/php /usr/local/lib/php
COPY --from=build /var/www/html /var/www/html

WORKDIR /var/www/html

# Permisos para Laravel
RUN chown -R nginx:nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer puerto
EXPOSE 80

# Arrancar PHP-FPM + Nginx
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]
