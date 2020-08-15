#!/bin/bash

yes | php artisan migrate
php artisan love:reaction-type-add --default
