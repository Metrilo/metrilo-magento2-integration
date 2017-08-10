#!/bin/bash

set -e

cd /magento2

composer require magento/product-community-edition 2.1.7 --no-update
composer update

bin/magento setup:upgrade
bin/magento setup:static-content:deploy
bin/magento cache:clean
bin/magento cache:flush
