#!/bin/bash

set -e

su -c "composer require metrilo/analytics-magento2-extension:master@dev" www-data
su -c "bin/magento module:enable --all --clear-static-content" www-data
su -c "bin/magento setup:upgrade" www-data
su -c "bin/magento cache:flush" www-data
su -c "bin/magento indexer:reindex" www-data

su -c /sbin/my_init
