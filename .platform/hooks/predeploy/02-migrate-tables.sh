#!/bin/bash

php artisan migrate:fresh --force
php artisan love:reaction-type-add --default
