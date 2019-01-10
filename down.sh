#!/usr/bin/env bash

# Colours
ORANGE='\033[0;33m'
BLACK='\033[0;30m'
LIME='\033[1;32m'
NC='\033[0m'

# Kills Laravel queue background process
pkill -f KurozoraQueue
echo -e "\n${BLACK}[${ORANGE}Kurozora${BLACK}] ${LIME}✔ ${NC}Laravel queue worker stopped!"