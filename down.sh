#!/usr/bin/env bash

# Colours
ORANGE='\033[0;33m'
BLACK='\033[0;30m'
LIME='\033[1;32m'
NC='\033[0m'

# Kills Laravel queue background process
kill $(ps aux | grep '[p]hp artisan queue:work' | awk '{print $2}')
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Laravel queue workers stopped!"

# Put application in maintenance mode
php artisan down --message="Server is being updated"