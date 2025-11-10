# Use official PHP image with Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev zip curl supervisor && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install dependencies and optimize
RUN composer install --no-dev --optimize-autoloader && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Ensure storage/logs is writable for scheduler
RUN chown -R www-data:www-data /var/www/html/storage

# Copy supervisor config
COPY ./supervisor/scheduler.conf /etc/supervisor/conf.d/scheduler.conf

# Expose port 80
EXPOSE 80

# Start Supervisor (which runs Apache and scheduler)
CMD ["/usr/bin/supervisord", "-n"]