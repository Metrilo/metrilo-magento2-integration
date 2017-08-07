#!/bin/bash

set -e

composer require metrilo/analytics-magento2-extension:master@dev --no-update
composer update

bin/magento setup:upgrade
bin/magento indexer:reindex
bin/magento cache:flush
