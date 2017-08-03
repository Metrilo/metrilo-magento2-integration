FROM alexcheng/magento2

RUN apt-get update
RUN apt-get install -y git-core

COPY scripts/env.php /var/www/html/app/etc
RUN chown www-data:www-data app/etc/env.php

USER www-data

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

ARG MAGE_DEV_UNAME
ENV MAGE_DEV_UNAME $MAGE_DEV_UNAME

ARG MAGE_DEV_PASSWORD
ENV MAGE_DEV_PASSWORD $MAGE_DEV_PASSWORD

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config http-basic.repo.magento.com $MAGE_DEV_UNAME $MAGE_DEV_PASSWORD

RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin
# RUN composer config repositories.magento composer https://repo.magento.com/packages.json

USER root

COPY scripts/post_deploy.sh /
RUN chmod +x /post_deploy.sh

CMD ["/post_deploy.sh"]
