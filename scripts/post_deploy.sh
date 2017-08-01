#!/bin/bash

set -e

su -c "bin/magento module:enable --all" www-data
su -c "bin/magento setup:upgrade" www-data
su -c "bin/magento setup:static-content:deploy" www-data
su -c "bin/magento cache:flush" www-data

su -c /sbin/my_init
