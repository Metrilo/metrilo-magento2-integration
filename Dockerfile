FROM alankent/gsd

USER root

RUN apt-get update
RUN apt-get install -y git-core

USER magento

RUN rm -rf vendor/

ARG MAGE2_MARKET_USERNAME
ARG MAGE2_MARKET_PASSWORD

RUN composer config http-basic.repo.magento.com $MAGE2_MARKET_USERNAME $MAGE2_MARKET_PASSWORD
# RUN composer require magento/product-community-edition 2.1.7 --no-update
# RUN composer update

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

RUN composer update

USER root
