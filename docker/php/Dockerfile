FROM php:8.3-fpm

ENV DEBIAN_FRONTEND noninteractive

ENV COMPOSER_MEMORY_LIMIT='-1'

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

WORKDIR /var/www/app

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]