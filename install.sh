#!/usr/bin/env bash

php n98-magerun.phar install \
    --noDownload \
    --dbHost="127.0.0.1" \
    --dbUser="root" \
    --dbPass="topsecret" \
    --dbName="kl_stock_test" \
    --useDefaultConfigParams=yes \
    --installationFolder="src/" \
    --baseUrl="http://localhost:8001/"

php -S localhost:8001 -t src/
