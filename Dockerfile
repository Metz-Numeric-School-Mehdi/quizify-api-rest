FROM php:8.4-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    zip \
    unzip \
    default-mysql-client

# Installer les extensions PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip curl intl

# Copier Composer depuis l'image officielle
COPY --from=composer/composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier les fichiers de l'application
COPY . .

# Installer les dépendances PHP avec Composer
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Donner les droits à www-data sur les dossiers nécessaires
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Ajouter le script de healthcheck et installer netcat
COPY healthcheck.sh /usr/local/bin/healthcheck.sh
RUN chmod +x /usr/local/bin/healthcheck.sh && apt-get install -y netcat-openbsd

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
