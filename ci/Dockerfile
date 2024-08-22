# Usar uma imagem base oficial do PHP com Apache
FROM php:8.2-apache

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    curl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar os arquivos da aplicação para o container
COPY . /var/www/html

# Instalar dependências do PHP usando o Composer
RUN composer install --no-dev --optimize-autoloader

# Configurar permissões corretas
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Habilitar mod_rewrite para o Laravel funcionar corretamente
RUN a2enmod rewrite

# Expôr a porta 80
EXPOSE 80

# Comando para iniciar o Apache
CMD ["apache2-foreground"]