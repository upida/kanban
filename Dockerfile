# Base image untuk PHP-FPM
FROM php:8.2-fpm as base

# Install dependensi sistem untuk PHP
RUN apt-get update && apt-get install -y \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy seluruh file proyek
COPY . .

# Install dependensi Laravel
RUN composer install --no-dev --optimize-autoloader

# Build assets Laravel
RUN npm install && npm run build

# Set izin untuk storage dan cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# ===========================
# Stage Nginx Configuration
# ===========================

# Base image untuk Nginx
FROM nginx:latest

# Copy file konfigurasi Nginx ke dalam container
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Copy semua file dari PHP stage
COPY --from=base /var/www /var/www

# Set izin folder storage
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port untuk Nginx
EXPOSE 80

# Jalankan Nginx
CMD ["nginx", "-g", "daemon off;"]
