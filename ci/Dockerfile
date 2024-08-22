# Use base PHP image
FROM php:8.2-apache

# Define path
WORKDIR /var/www/html

# IInstall system dependencies
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
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Extentions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip bz2 sodium intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy files to container
COPY . /var/www/html

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Configure right permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Enable mod_rewrite to Laravel work correctly
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80

# Entrypoint
ENTRYPOINT ["entrypoint.sh"]