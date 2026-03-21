FROM php:8.2-cli

WORKDIR /app

# Atualizar e instalar dependências
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install zip pdo pdo_mysql

# Instalar Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar projeto
COPY . .

# Instalar dependências Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Permissões (IMPORTANTE pro Laravel)
RUN chmod -R 775 storage bootstrap/cache

# Porta do Render
EXPOSE 10000

# Rodar aplicação
CMD php artisan serve --host=0.0.0.0 --port=10000