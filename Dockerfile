# Use official PHP image with extensions
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git curl libpq-dev zip unzip \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy app files
COPY . .

# Install dependencies
RUN composer install --no-interaction --prefer-dist

# Set proper permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]

CMD php artisan migrate --seed && php-fpm

