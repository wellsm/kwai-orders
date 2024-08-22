#!/bin/bash

# Criar application key
php artisan key:generate

# Executar as migrações
php artisan migrate --force

# Iniciar o Apache
apache2-foreground
