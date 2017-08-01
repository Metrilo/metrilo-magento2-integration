#!/bin/bash
# Run once on first time database setup
# The database data is used in env.php file and mounted
# into the magento installation

su www-data

bin/magento setup:install \
  --base-url="http://mage2-test.metrilo.com" \
  --base-url-secure="https://mage2-test.metrilo.com" \
  --db-host="magento2-testenv-db" \
  --db-name="magento" \
  --db-user="magento" \
  --db-password="magento" \
  --admin-firstname="admin" \
  --admin-lastname="admin" \
  --admin-email="admin@metrilo.com" \
  --admin-user="admin" \
  --admin-password="adminMET123" \
  --language="en_US" \
  --currency="USD" \
  --timezone="America/Chicago" \
  --backend-frontname="admin"
