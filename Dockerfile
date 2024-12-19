# Use the PHP-FPM image as the base
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    zip unzip git libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev nginx \
    && docker-php-ext-install mysqli gd mbstring pdo_mysql \
    && docker-php-ext-enable mysqli

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set document root
WORKDIR /var/www/html

# Copy WordPress files
COPY . .

# Copy .env.example to .env if .env does not exist
RUN cp .env.example .env

# Install PHP dependencies using Composer
RUN composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
RUN npm install && npm run build

# Expose port 8000
EXPOSE 8000

# Run Laravel server
CMD php artisan serve --host=0.0.0.0 --port=8000
