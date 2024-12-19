# ===========================
# Stage 1: Build Frontend
# ===========================
FROM node:18 AS node_stage

# Set working directory untuk frontend
WORKDIR /app

# Copy file frontend (package.json, vite.config.js, dll.)
COPY package*.json ./
COPY vite.config.js ./
COPY resources ./resources

# Install dependensi frontend dan build assets
RUN npm install && npm run build

# ===========================
# Stage 2: PHP-FPM
# ===========================
FROM php:8.2-fpm AS php_stage

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

# Set working directory untuk aplikasi Laravel
WORKDIR /var/www

# Copy semua file proyek Laravel
COPY . .

# Copy hasil build frontend dari stage 1
COPY --from=node_stage /app/public /var/www/public

# Install dependensi Laravel menggunakan Composer
RUN composer install --no-dev --optimize-autoloader

# Set izin untuk folder storage dan bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# ===========================
# Stage 3: Nginx Configuration
# ===========================
FROM nginx:latest

# Copy file konfigurasi Nginx ke dalam container
COPY ./nginx.conf /etc/nginx/conf.d/default.conf

# Copy semua file dari stage PHP-FPM
COPY --from=php_stage /var/www /var/www

# Set izin untuk folder storage
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Expose port untuk Nginx
EXPOSE 80

# Jalankan Nginx
CMD ["nginx", "-g", "daemon off;"]
