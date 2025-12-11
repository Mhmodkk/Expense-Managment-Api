FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsodium-dev \
    default-mysql-client \
    default-libmysqlclient-dev

RUN docker-php-ext-install pdo_mysql mbstring bcmath zip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader

# Node? مو لازم لأن مشروعك API وليس فيه frontend

# هذا أهم بخطوة: Railway يعطيك PORT ويجب تمريره للـ serve
CMD php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT}
