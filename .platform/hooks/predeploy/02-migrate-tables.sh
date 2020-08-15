#!/bin/bash

php artisan migrate -n
php artisan love:reaction-type-add --default
