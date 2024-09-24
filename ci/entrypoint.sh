#!/bin/sh

# Create Application key and run migrations
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link

# Cache Configs, Events, Routes and Views
php artisan optimize
php artisan route:clear

# Start supervisord
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf