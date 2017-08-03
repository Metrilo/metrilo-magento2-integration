#!/bin/bash

set -e

su -c "composer require metrilo/analytics-magento2-extension:master@dev" www-data
su -c "bin/magento module:enable --all --clear-static-content" www-data

# Sample data
# su -c "bin/magento sampledata:deploy" www-data
# su -c "bin/magento setup:upgrade" www-data

# Flush the cache
su -c "bin/magento cache:flush" www-data
su -c "bin/magento deploy:mode:set developer" www-data
su -c "bin/magento setup:static-content:deploy" www-data
su -c "bin/magento indexer:reindex" www-data

su -c /sbin/my_init
