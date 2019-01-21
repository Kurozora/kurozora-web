#!/usr/bin/env bash

# Colours
ORANGE='\033[0;33m'
BLACK='\033[0;30m'
LIME='\033[1;32m'
NC='\033[0m'

# Remove application from maintenance mode
php artisan up

# Starts Laravel queue background process
(trap "" SIGINT; exec -a KurozoraQueue nohup php artisan queue:work &>/dev/null &)
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Laravel queue worker started!"

# Done updating
echo -e "\n${ORANGE}Updated to Kurozora `grep -o "'version' => '\(.*\)'" config/app.php`!"
echo -e "\n${BLACK}___________________________________"