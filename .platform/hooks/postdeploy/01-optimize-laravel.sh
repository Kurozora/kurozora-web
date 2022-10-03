#!/bin/bash

# Clear expired password reset tokens
php artisan auth:clear-resets

# Clear and cache config
php artisan config:cache

# Clear and cache views
php artisan view:cache

# Clear and cache routes
php artisan route:cache

# Clear and cache events
php artisan event:cache
