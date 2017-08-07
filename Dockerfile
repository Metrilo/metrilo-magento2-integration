FROM alankent/gsd

USER root

RUN apt-get update
RUN apt-get install -y git-core

COPY scripts/update_plugin.sh /
RUN chown magento:magento /update_plugin.sh

USER magento

RUN rm -rf vendor/

ARG MAGE2_REPO_USERNAME
ENV MAGE2_REPO_USERNAME $MAGE2_REPO_USERNAME

RUN echo $MAGE2_REPO_USERNAME

ARG MAGE2_REPO_PASSWORD
ENV MAGE2_REPO_PASSWORD $MAGE2_REPO_PASSWORD

# RUN echo $MAGENTO_REPO_PASSWORD

RUN composer config http-basic.repo.magento.com $MAGE2_REPO_USERNAME $MAGE2_REPO_PASSWORD
# RUN composer require magento/product-community-edition 2.1.7 --no-update

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

RUN composer update

RUN source /update_plugin.sh
