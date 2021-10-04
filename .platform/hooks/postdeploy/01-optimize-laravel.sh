#!/bin/bash

# Clear caches
php artisan cache:clear

# Clear expired password reset tokens
php artisan auth:clear-resets

# Clear and cache routes
php artisan route:cache

# Clear and cache events
php artisan events:cache

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache
