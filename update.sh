# Colours
ORANGE='\033[0;33m'
BLACK='\033[0;30m'
LIME='\033[1;32m'
NC='\033[0m'

# Git pull
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Updating source from repository..."
git pull origin master --quiet
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Source updated!"

# Composer dump autoload
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${NC}Dumping autoload..."
composer dump-autoload --quiet
echo -e "${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Autoload file dumped!"

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