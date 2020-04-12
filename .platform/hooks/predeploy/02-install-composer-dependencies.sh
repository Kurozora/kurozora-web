#!/bin/bash

# Install Composer dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install -d /var/www/html/ --optimize-autoloader --no-dev
