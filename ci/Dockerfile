# Use base PHP image
FROM php:8.3-fpm as base

# Define path
WORKDIR /var/www/app

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
    procps \
    supervisor \
    nodejs \
    npm \
    cron 

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Extentions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip bz2 sodium intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy files to container
COPY . /var/www/app

ENV APP_LOCALE='pt_BR'

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install NPM dependencies and build assets
RUN npm install
RUN npm run build

# Configure right permissions
RUN chown -R www-data:www-data /var/www/app/storage /var/www/app/bootstrap/cache

# Copy NGINX and Supervisor files
COPY ci/default.conf /etc/nginx/sites-enabled/default
COPY ci/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY ci/entrypoint.sh /usr/local/bin/entrypoint.sh

# Give execution permissions to the entrypoint script
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 80
EXPOSE 80

# Use the entrypoint script
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]