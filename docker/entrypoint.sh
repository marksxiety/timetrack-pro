#!/bin/sh
set -e

echo "Linking storage directory..."
php artisan storage:link

echo "Running migrations..."
php artisan migrate --force

echo "Caching config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel development server..."
php artisan serve --host=0.0.0.0 --port=8000
