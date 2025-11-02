#!/usr/bin/env bash

# Go to backend folder
cd backend

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations (optional; you can also do this manually)
php artisan migrate --force

# Start Laravel server using the port Railway assigns

php artisan serve --host 0.0.0.0 --port $PORT 58080