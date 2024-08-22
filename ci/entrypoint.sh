#!/bin/bash

# Generate Application key
php artisan key:generate

# Execute migrations
php artisan migrate --force

# Start Apache
apache2-foreground
