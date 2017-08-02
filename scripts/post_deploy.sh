#!/bin/bash

set -e

su -c "composer update metrilo/analytics-magento2-extension:master@dev" www-data
su -c "bin/magento module:enable --all" www-data
su -c "composer config repositories.magento composer https://repo.magento.com/packages.json" www-data
su -c "bin/magento sampledata:deploy" www-data
su -c "bin/magento setup:upgrade" www-data
su -c "bin/magento setup:static-content:deploy" www-data
su -c "bin/magento cache:flush" www-data

su -c /sbin/my_init
