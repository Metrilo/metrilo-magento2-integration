FROM alankent/gsd

USER root

RUN apt-get update

RUN apt-get install -y git-core

USER www-data

RUN rm -rf vendor/

RUN composer require magento/product-community-edition 2.1.7 --no-update
RUN composer update

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

USER root
