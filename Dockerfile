FROM php:8.2-cli

WORKDIR /app

# Instalar dependências
RUN apt-get update && apt-get install -y \
    unzip git curl libzip-dev zip \
    && docker-php-ext-install zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar projeto
COPY . .

# Instalar Laravel
RUN composer install

# Expor porta
EXPOSE 10000

# Rodar Laravel
CMD php artisan serve --host=0.0.0.0 --port=10000