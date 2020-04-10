#!/bin/bash

# Enter html directory
cd /var/www/html/

# Create cache and chmod folders
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache

# Install Composer dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install -d /var/www/html/

# Symlink .env configuration
ln -s ~/.env /var/www/html/.env

# Migrate all tables
php /var/www/html/artisan migrate

# Clear any previous cached views
php /var/www/html/artisan config:clear
php /var/www/html/artisan cache:clear
php /var/www/html/artisan view:clear

# Optimize the application
php /var/www/html/artisan config:cache
php /var/www/html/artisan optimize

# Change rights
chmod 777 -R /var/www/html/bootstrap/cache
chmod 777 -R /var/www/html/storage
chmod 777 -R /var/www/html/public/files/

# Bring up application
php /var/www/html/artisan up
