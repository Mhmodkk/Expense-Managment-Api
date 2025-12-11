FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsodium-dev \
    default-mysql-client \
    default-libmysqlclient-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sodium

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# NodeJS (اختياري)
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash \
    && apt-get update \
    && apt-get install -y nodejs

WORKDIR /var/www/html

COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# إذا عندك Frontend مع Vite شغّل:
# RUN npm install && npm run build

EXPOSE 8000

# Laravel CMD
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8000
