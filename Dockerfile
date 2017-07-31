FROM alexcheng/magento2

RUN apt-get update
RUN apt-get install -y git

USER www-data

RUN composer config github-oauth.github.com "53951ecc51f64c62bfb2b2ba9dc9e7f75696b978"
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin
RUN composer require metrilo/analytics-magento2-extension:master --no-update
RUN bin/magento module:enable --all

COPY scripts/env.php /var/www/html/app/etc

USER root
