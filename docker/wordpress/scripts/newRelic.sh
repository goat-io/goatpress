#!/usr/bin/env bash

################################
# NEW RELIC INSTALLATION
################################
 mkdir -p /var/log/newrelic /var/run/newrelic && \
    touch /var/log/newrelic/php_agent.log /var/log/newrelic/newrelic-daemon.log && \
    chmod -R g+ws /tmp /var/log/newrelic/ /var/run/newrelic/ && \
    chown -R 1001:0 /tmp /var/log/newrelic/ /var/run/newrelic/ && \

    # Download and install Newrelic binary
    export NEWRELIC_VERSION=$(curl -sS https://download.newrelic.com/php_agent/release/ | sed -n 's/.*>\(.*linux-musl\).tar.gz<.*/\1/p') && \
    cd /tmp && curl -sS "https://download.newrelic.com/php_agent/release/${NEWRELIC_VERSION}.tar.gz" | gzip -dc | tar xf - && \
    export NR_INSTALL_USE_CP_NOT_LN=1 && \
    export NR_INSTALL_SILENT=1 && \
    cd "${NEWRELIC_VERSION}" && \
    ./newrelic-install install && \
    rm -f /var/run/newrelic-daemon.pid && \
    rm -f /tmp/.newrelic.sock

    sed -i \
    -e "s/newrelic.license =.*/newrelic.license = "b5f4cf26dd3bff635bf02bff025a1cf8FFFFNRAL"/" \
    -e "s/newrelic.appname =.*/newrelic.appname = "goat"/" \
    /etc/php8/conf.d/newrelic.ini \
    -e "s/;newrelic.loglevel =.*/newrelic.loglevel = info/" \
    /etc/php8/conf.d/newrelic.ini \
    -e "s/;newrelic.daemon.loglevel =.*/newrelic.daemon.loglevel = info/" \
    /etc/php8/conf.d/newrelic.ini \
    -e "s/newrelic.daemon.utilization.detect_docker =.*/newrelic.daemon.utilization.detect_docker = true/" \
    /etc/php8/conf.d/newrelic.ini

    sed -i \
        -e 's/;newrelic.daemon.app_connect_timeout =.*/newrelic.daemon.app_connect_timeout=15s/' \
        -e 's/;newrelic.daemon.start_timeout =.*/newrelic.daemon.start_timeout=5s/' \
        /etc/php8/conf.d/newrelic.ini