#!/bin/bash

set -e

cd /magento2

composer require magento/product-community-edition 2.1.7 --no-update
composer update

bin/magento setup:upgrade
bin/magento indexer:reindex
bin/magento cache:flush
