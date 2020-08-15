#!/bin/bash

/usr/local/bin/composer dump-autoload
yes | php artisan migrate:reset --force
yes | php artisan migrate
php artisan love:reaction-type-add --default
