FROM php:8.3-fpm

# Dépendances système
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 🔥 Copier uniquement le code Laravel
COPY ./src /var/www

# Installer les dépendances
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Générer .env si absent
RUN cp .env.example .env || true

# Générer clé Laravel
RUN php artisan key:generate || true

# Permissions (très important)
RUN chown -R www-data:www-data /var/www \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

CMD ["php-fpm"]