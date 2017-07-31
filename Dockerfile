FROM alexcheng/magento2

RUN apt-get update
RUN apt-get install -y git

RUN  echo "    IdentityFile ~/.ssh/id_rsa" >> /etc/ssh/ssh_config

USER www-data

RUN mkdir -p /tmp/.ssh
COPY docker/.ssh /tmp/.ssh
RUN ssh-agent $(ssh-add /tmp/.ssh/id_rsa.pub)

RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin
RUN composer require metrilo/analytics-magento2-extension:master --no-update
RUN ssh-agent $(ssh-add /tmp/.ssh/id_rsa.pub && composer update && composer install)
RUN bin/magento module:enable --all
RUN bin/magento setup:di:compile

RUN rm -rf /tmp/.ssh

USER root
