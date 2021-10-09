#!/bin/bash

# Install Composer dependencies
export COMPOSER_ALLOW_SUPERUSER=1
/usr/local/bin/composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
