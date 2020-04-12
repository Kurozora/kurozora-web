#!/bin/bash

# Create folders
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache

# Set nginx as owner of the application
chown -R nginx:nginx /var/www/html

# All files to 644
sudo find /var/www/html -type f -exec chmod 644 {} \;

# All directories to 755
sudo find /var/www/html -type d -exec chmod 755 {} \;
