#!/bin/bash

# Create cache and chmod folders
mkdir -p /var/www/html/bootstrap/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache

chmod 777 -R /var/www/html/bootstrap/cache
chmod 777 -R /var/www/html/storage
chmod 777 -R /var/www/html/public/files/
