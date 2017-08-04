#!/bin/bash

set -e

if [[ -z "${RELEASE_VERSION}" ]]; then
  echo "RELEASE_VERSION environment variable must be set. Existing..."
  exit 1
fi

if [[ -z "${GITHUB_TOKEN}" ]]; then
  echo "GITHUB_TOKEN environment variable must be set. Existing..."
  exit 1
fi

if [[ -z "${MAGE2_MARKET_USERNAME}" ]]; then
  echo "MAGE2_MARKET_USERNAME environment variable must be set. Existing..."
  exit 1
fi

if [[ -z "${MAGE2_MARKET_PASSWORD}" ]]; then
  echo "MAGE2_MARKET_PASSWORD environment variable must be set. Existing..."
  exit 1
fi

scripts_dir=$(dirname ${BASH_SOURCE[0]})
app_dir=$(dirname ${scripts_dir})

docker_registry='metrilo.azurecr.io'
image_name='metrilo/magento2-plugin'

docker build \
    -f Dockerfile \
    -t $image_name:$RELEASE_VERSION \
    --build-arg GITHUB_TOKEN=$GITHUB_TOKEN \
    --build-arg MAGE2_MARKET_USERNAME=$MAGE2_MARKET_USERNAME \
    --build-arg MAGE2_MARKET_PASSWORD=$MAGE2_MARKET_PASSWORD \
    $app_dir

docker tag $image_name:$RELEASE_VERSION $docker_registry/$image_name:$RELEASE_VERSION
docker push $docker_registry/$image_name:$RELEASE_VERSION
