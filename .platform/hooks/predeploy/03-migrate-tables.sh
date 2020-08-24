#!/bin/bash

php artisan migrate --force
php artisan love:reaction-type-add --default
php artisan love:reaction-type-add --name=Heart --mass=1
