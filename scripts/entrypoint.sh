#!/bin/bash

set -e

exec /entrypoint.sh "$@"
chmod +x /magento2/bin/magento

su -c "exec /update_plugin.sh" magento
