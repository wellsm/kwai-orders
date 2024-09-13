#!/bin/sh

# Create Application key and run migrations
php artisan key:generate --force
php artisan migrate --force

# Optimize Composer
composer dump-autoload --optimize

# Cache Configs, Events, Routes and Views
php artisan optimize

# Start supervisord
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf