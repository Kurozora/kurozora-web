#!/bin/bash

# Install Composer dependencies
export COMPOSER_ALLOW_SUPERUSER=1
composer install --optimize-autoloader --no-dev
