#!/bin/bash

set -e

su -c "composer require metrilo/analytics-magento2-extension:master@dev" www-data
su -c "bin/magento module:enable --all" www-data
# Update the modules
su -c "bin/magento setup:upgrade" www-data
# Flush the cache
su -c "bin/magento cache:flush" www-data
# Deploy static content and reindex
su -c "bin/magento setup:static-content:deploy" www-data
su -c "bin/magento indexer:reindex" www-data
