#!/bin/bash

# Clear any previous cached views
php artisan cache:clear
php artisan view:clear

# Cache the config
php artisan config:cache
