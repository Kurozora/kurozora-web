#!/usr/bin/env bash

# Colours
ORANGE='\033[0;33m'
BLACK='\033[0;30m'
LIME='\033[1;32m'
NC='\033[0m'

# Run down
. down.sh

# Git pull
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Updating source from repository..."
git checkout . --quiet
git pull origin master --quiet
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Source updated!"

# Composer install
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Composer install..."
composer install --quiet
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Composer dependencies installed/updated!"

# Composer dump autoload
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Dumping autoload..."
composer dump-autoload --quiet
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Autoload file dumped!"

# Clear laravel config cache
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Reloading Laravel cache..."
php artisan view:clear
php artisan view:cache
php artisan config:cache
php artisan cache:clear
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Cache reloaded!"

# Migrate fresh
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Would you like to run a fresh migration? (y/n): "
read migrateResponse

if [ $migrateResponse == 'y' ]
then
    echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Running fresh migration..."
    php artisan migrate:fresh --seed
    echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Migrations finished!"
fi

# Finished
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Finished updating."

# Run up
. up.sh