FROM alankent/gsd

USER root

RUN apt-get update
RUN apt-get install -y git-core

COPY scripts/update_plugin.sh /
RUN chown magento:magento /update_plugin.sh

RUN mkdir -p /var/lib/mysql
# RUN chown mysql:mysql /var/lib/mysql

# USER mysql
# VOLUME ["/var/lib/mysql"]

USER magento

# VOLUME ["/magento2/pub"]
# VOLUME ["/magento2/var"]

RUN rm -rf vendor/

ARG MAGE2_REPO_USERNAME
ENV MAGE2_REPO_USERNAME $MAGE2_REPO_USERNAME

ARG MAGE2_REPO_PASSWORD
ENV MAGE2_REPO_PASSWORD $MAGE2_REPO_PASSWORD

RUN composer config http-basic.repo.magento.com $MAGE2_REPO_USERNAME $MAGE2_REPO_PASSWORD
# RUN composer require magento/product-community-edition 2.1.7 --no-update

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

RUN composer update

RUN chmod +x bin/magento
#
RUN composer require metrilo/analytics-magento2-extension:master@dev --no-update
RUN composer update

USER root
