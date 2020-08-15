#!/bin/bash

/usr/local/bin/composer dump-autoload
php artisan migrate:rollback
yes | php artisan migrate
php artisan love:reaction-type-add --default
