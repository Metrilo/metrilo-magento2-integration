#!/bin/bash

set -e

cd /magento2

composer require metrilo/analytics-magento2-extension:master@dev --no-update
composer update

bin/magento setup:upgrade

rm -rf /magento2/var/di

bin/magento setup:di:compile
bin/magento setup:static-content:deploy
bin/magento indexer:reindex
bin/magento cache:flush
