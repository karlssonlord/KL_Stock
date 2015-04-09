#!/usr/bin/env bash

red=`tput setaf 1`
green=`tput setaf 2`
reset=`tput sgr0`

# Install composer
echo "${green}Installing composer dependencies...${reset}"
composer install --prefer-source --no-interaction

echo "${green}Removing old database...${reset}"
mysql -uroot -ptopsecret -e "drop database if exists kl_stock_test;"

# Remove
if [ -f "./src/app/etc/local.xml" ]; then
    echo "${green}Removing old local.xml config...${reset}"
    rm -rf src/app/etc/local.xml
fi

# Install n98-magerun tool
if [ ! -f "./n98-magerun.phar" ]; then
    echo "${green}Downloading n98-magerun...${reset}"
    curl "https://raw.githubusercontent.com/netz98/n98-magerun/master/n98-magerun.phar" -o "n98-magerun.phar"
fi

php n98-magerun.phar install \
    --noDownload \
    --dbHost="127.0.0.1" \
    --dbUser="root" \
    --dbPass="topsecret" \
    --dbName="kl_stock_test" \
    --useDefaultConfigParams=yes \
    --installationFolder="src/" \
    --baseUrl="http://localhost:8001/"

if ps aux | grep "[p]hp -S localhost:8001 -t src/" > /dev/null ; then
    echo "${green}Server is already running...${reset}"
else
    php -S localhost:8001 -t src/ &
fi

echo "${green}Done installing.${reset}"