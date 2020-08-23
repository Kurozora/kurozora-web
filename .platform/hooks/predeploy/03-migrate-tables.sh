#!/bin/bash

php artisan migrate --force
php artisan love:reaction-type-add --default
