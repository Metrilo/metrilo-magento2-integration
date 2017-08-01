#!/bin/bash

set -e

if [[ -z "${RELEASE_VERSION}" ]]; then
  echo "RELEASE_VERSION environment variable must be set. Existing..."
  exit 1
fi

ktmpl kube/magento2-plugin-deployment.ktmpl.yaml --parameter RELEASE_VERSION $RELEASE_VERSION | kubectl replace -f -
