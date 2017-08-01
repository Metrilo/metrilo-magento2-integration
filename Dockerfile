FROM alexcheng/magento2

RUN apt-get update
RUN apt-get install -y git

COPY scripts/env.php /var/www/html/app/etc
RUN chown www-data:www-data app/etc/env.php

USER www-data

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin
RUN composer require metrilo/analytics-magento2-extension:master@dev

USER root

COPY scripts/post_deploy.sh /
RUN chmod +x /post_deploy.sh

CMD ["/post_deploy.sh"]
