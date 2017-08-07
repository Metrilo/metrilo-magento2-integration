FROM alankent/gsd

USER root

RUN apt-get update
RUN apt-get install -y git-core

USER magento

RUN rm -rf vendor/

ARG MAGE2_REPO_USERNAME
ENV MAGENTO_REPO_USERNAME $MAGE2_REPO_USERNAME

ARG MAGE2_REPO_PASSWORD
ENV MAGENTO_REPO_PASSWORD $MAGE2_REPO_PASSWORD

RUN composer config http-basic.repo.magento.com $MAGE2_REPO_USERNAME $MAGE2_REPO_PASSWORD

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

RUN composer update

USER root

COPY scripts/update_plugin.sh /
