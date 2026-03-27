FROM php:8.4-fpm

# Instalar dependências do sistema
# ADICIONADO: libsqlite3-dev para o pdo_sqlite funcionar
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nginx \
    libsqlite3-dev


RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .


RUN composer install --no-dev --optimize-autoloader


RUN mkdir -p database && touch database/database.sqlite


RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache database

EXPOSE 80

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80