# Etapa 1: Construcción (Composer + Node + PHP)
FROM php:8.2-fpm as build

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    libonig-dev libxml2-dev ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd bcmath opcache

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

WORKDIR /var/www/html

# Copiar archivos del proyecto
COPY . .

# Instalar dependencias de PHP y JS
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist
RUN npm ci --silent && npm run build

# Cachear configuración de Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Etapa 2: Producción (PHP-FPM + Nginx)
FROM php:8.2-fpm-alpine as production

# Instalar Nginx, bash y netcat para checks
RUN apk add --no-cache nginx bash netcat-openbsd

# Limpiar posibles configs en conf.d (evita duplicados)
RUN rm -f /etc/nginx/conf.d/* || true

# Crear carpeta para run/nginx
RUN mkdir -p /run/nginx /var/www/html/storage /var/www/html/bootstrap/cache

# Copiar /sobrescribir la config principal de nginx (incluye server block)
COPY ./nginx.conf /etc/nginx/nginx.conf

# Copiar script de arranque
COPY ./start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Copiar proyecto desde la etapa build
COPY --from=build /var/www/html /var/www/html

WORKDIR /var/www/html

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Puerto que usará Render
EXPOSE 80

# START
CMD ["/usr/local/bin/start.sh"]
