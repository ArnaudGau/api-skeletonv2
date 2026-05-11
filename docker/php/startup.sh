#!/bin/sh

# attendre que mysql soit prêt
while ! php artisan migrate:status > /dev/null 2>&1; do
    sleep 3
done

echo "✅ MySQL is ready"

# migrations
php artisan migrate --force

# seeders
php artisan db:seed --force

# optimisations
php artisan optimize

echo "🚀 PHP-FPM démarré"

exec php-fpm