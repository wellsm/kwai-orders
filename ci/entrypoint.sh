#!/bin/sh

# Create Application key and run migrations
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link

# Cache Configs, Events, Routes and Views
php artisan optimize

# Task Scheduling
echo "* * * * * www-data /usr/local/bin/php /var/www/app/artisan schedule:run >> /dev/null 2>&1" > /etc/cron.d/laravel

# Permission to crontab
chmod 0644 /etc/cron.d/laravel

# Start cron
crontab /etc/cron.d/laravel

# Start supervisord
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf