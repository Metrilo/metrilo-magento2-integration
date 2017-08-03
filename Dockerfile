FROM alexcheng/magento2

RUN apt-get update
RUN apt-get install -y git-core

COPY scripts/env.php /var/www/html/app/etc
RUN chown www-data:www-data app/etc/env.php

USER www-data

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

USER root

COPY scripts/post_deploy.sh /
RUN chmod +x /post_deploy.sh

CMD ["/post_deploy.sh"]
