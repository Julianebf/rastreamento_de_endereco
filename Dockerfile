FROM php:8.4-fpm

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip nginx

# Instalar extensões PHP (Adicionei pdo_sqlite já que seu log mostrou erro de SQLite)
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Instalar dependências do Composer
RUN composer install --no-dev --optimize-autoloader

# --- CORREÇÕES AQUI ---
# 1. Cria o arquivo do banco de dados se ele não existir
RUN mkdir -p database && touch database/database.sqlite

# 2. Ajusta permissões (importante para o SQLite conseguir escrever no arquivo)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage \
    && chmod -R 775 /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/database

# O Render usa a variável $PORT, mas o comando serve do Laravel 
# geralmente precisa de um ajuste para rodar em produção.
EXPOSE 80

# Comando para rodar migrações e iniciar o servidor
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80