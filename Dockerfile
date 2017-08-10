FROM alankent/gsd

USER root

RUN apt-get update
RUN apt-get install -y git-core software-properties-common wget

RUN echo 'deb http://packages.dotdeb.org jessie all' >> /etc/apt/sources.list
RUN echo 'deb-src http://packages.dotdeb.org jessie all' >> /etc/apt/sources.list

RUN wget https://www.dotdeb.org/dotdeb.gpg
RUN apt-key add dotdeb.gpg
RUN rm dotdeb.gpg

RUN apt-get update
RUN apt-get install -y php7.0

RUN echo php -v

COPY scripts/update_plugin.sh /
COPY scripts/update_magento.sh /

RUN chown magento:magento /update_plugin.sh
RUN chown magento:magento /update_magento.sh

USER mysql
VOLUME ["/var/lib/mysql"]

USER magento
VOLUME ["/magento2/pub"]
VOLUME ["/magento2/var"]

RUN rm -rf vendor/

ARG MAGE2_REPO_USERNAME
ENV MAGE2_REPO_USERNAME $MAGE2_REPO_USERNAME

ARG MAGE2_REPO_PASSWORD
ENV MAGE2_REPO_PASSWORD $MAGE2_REPO_PASSWORD

RUN composer config http-basic.repo.magento.com $MAGE2_REPO_USERNAME $MAGE2_REPO_PASSWORD

ARG GITHUB_TOKEN
ENV GITHUB_TOKEN $GITHUB_TOKEN

RUN composer config github-oauth.github.com $GITHUB_TOKEN
RUN composer config repositories.repo-name vcs https://github.com/metrilo/magento2-plugin

RUN composer require metrilo/analytics-magento2-extension:master@dev --no-update
RUN composer update

USER root

COPY scripts/entrypoint.sh /mage2_docker_entrypoint.sh
RUN chmod +x /mage2_docker_entrypoint.sh

ENTRYPOINT ["/mage2_docker_entrypoint.sh"]
