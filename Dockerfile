# Etapa 1: Construcción (Composer + Node + PHP)
FROM php:8.3-fpm as build

# Instalar dependencias del sistema (Debian)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev zip git unzip curl \
    libonig-dev libxml2-dev libzip-dev ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd bcmath opcache zip \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Instalar Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Instalar dependencias de PHP y JS (producción)
RUN composer install --no-dev --optimize-autoloader --no-interaction
RUN npm ci --silent && npm run build


# Etapa 2: Producción (PHP-FPM + Nginx - Alpine)
FROM php:8.3-fpm-alpine as production

# Instalar extensiones necesarias en producción
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Nginx y utilidades
RUN apk add --no-cache nginx bash

# Crear carpeta run y limpiar conf.d para evitar duplicados
RUN mkdir -p /run/nginx /var/www/html/storage /var/www/html/bootstrap/cache \
    && rm -f /etc/nginx/conf.d/* || true

# Copiar configuración principal y site
COPY ./nginx.conf /etc/nginx/nginx.conf
COPY ./default.conf /etc/nginx/conf.d/default.conf

# Copiar script de inicio
COPY ./start.sh /start.sh
RUN chmod +x /start.sh

# Copiar proyecto desde la etapa build
COPY --from=build /var/www/html /var/www/html

WORKDIR /var/www/html

# Permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["sh", "/start.sh"]