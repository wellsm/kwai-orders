# Use base PHP image
FROM php:8.3-fpm as base

# Define path
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libbz2-dev \
    libsodium-dev \
    zip \
    unzip \
    nginx \
    supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Extentions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip bz2 sodium intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy files to container
COPY . /var/www/html

ENV APP_LOCALE='pt_BR'

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure right permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Copy NGINX and Supervisor files
COPY ci/nginx.conf /etc/nginx/nginx.conf
COPY ci/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port 8080
EXPOSE 8080

# Entrypoint
ENTRYPOINT ["/bin/sh", "-c" , "php artisan key:generate && php artisan migrate --force && exec supervisord -c /etc/supervisor/conf.d/supervisord.conf"]