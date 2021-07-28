## Inspiration I: https://github.com/khromov/alpine-nginx-php8
## Inspiration II: https://github.com/openbridge/ob_php-fpm
## Inspiration III: https://github.com/openbridge/nginx

FROM alpine:3.13
MAINTAINER Ignacio Cabrera (ignacio.cabrera@goatlab.io)

ARG NGINX_VERSION="1.19.9"
ARG ALPINE_VERSION=3.11
ARG PHP_VERSION="8.0.2-r0"

ENV VAR_PREFIX=/var/run \
    LOG_PREFIX=/var/log/nginx \
    TEMP_PREFIX=/tmp \
    CACHE_PREFIX=/var/cache \
    CONF_PREFIX=/etc/nginx \
    CERTS_PREFIX=/etc/pki/tls \
    PHP_LOG_PREFIX=/var/log/php-fpm


############################################################
# NGINX
############################################################

RUN set -x  \
  && CONFIG="\
    --prefix=/usr/share/nginx/ \
    --sbin-path=/usr/sbin/nginx \
    --add-module=/tmp/naxsi/naxsi_src \
    --modules-path=/usr/lib/nginx/modules \
    --conf-path=${CONF_PREFIX}/nginx.conf \
    --error-log-path=${LOG_PREFIX}/error.log \
    --http-log-path=${LOG_PREFIX}/access.log \
    --pid-path=${VAR_PREFIX}/nginx.pid \
    --lock-path=${VAR_PREFIX}/nginx.lock \
    --http-client-body-temp-path=${TEMP_PREFIX}/client_temp \
    --http-proxy-temp-path=${TEMP_PREFIX}/proxy_temp \
    --http-fastcgi-temp-path=${TEMP_PREFIX}/fastcgi_temp \
    --http-uwsgi-temp-path=${TEMP_PREFIX}/uwsgi_temp \
    --http-scgi-temp-path=${TEMP_PREFIX}/scgi_temp \
    --user=nobody \
    --group=nobody \
    --with-http_ssl_module \
    --with-pcre-jit \
    --with-http_realip_module \
    --with-http_addition_module \
    --with-http_sub_module \
    --with-http_dav_module \
    --with-http_flv_module \
    --with-http_mp4_module \
    --with-http_gunzip_module \
    --with-http_gzip_static_module \
    --with-http_random_index_module \
    --with-http_secure_link_module \
    --with-http_stub_status_module \
    --with-http_auth_request_module \
    --with-http_xslt_module=dynamic \
    --with-http_image_filter_module=dynamic \
    --with-http_geoip_module=dynamic \
    --with-threads \
    --with-stream \
    --with-stream_ssl_module \
    --with-stream_ssl_preread_module \
    --with-stream_realip_module \
    --with-stream_geoip_module=dynamic \
    --with-http_slice_module \
    --with-mail \
    --with-mail_ssl_module \
    --with-compat \
    --with-file-aio \
    --with-http_v2_module \
    --add-module=/tmp/ngx_cache_purge-2.3 \
    --add-module=/tmp/ngx_http_redis-0.3.9 \
    --add-module=/tmp/redis2-nginx-module-0.15 \
    --add-module=/tmp/srcache-nginx-module-0.31 \
    --add-module=/tmp/echo-nginx-module \
    --add-module=/tmp/ngx_devel_kit-0.3.1 \
    --add-module=/tmp/set-misc-nginx-module-0.32 \
    --add-module=/tmp/ngx_brotli \
    --with-ld-opt='-L/usr/lib' \
    --with-cc-opt=-Wno-error \
  " \
  && apk add --no-cache --virtual .build-deps \
      xz \
      dcron \
      htop \
      icu-libs \
      libxml2-dev \ 
      libmcrypt-dev \
      alpine-sdk \
      autoconf \
      automake \
      binutils  \
      build-base  \
      build-base \
      ca-certificates \
      cmake  \
      findutils \
      gcc  \
      gd-dev \
      geoip-dev \
      gettext \
      git \
      gnupg  \
      gnupg \
      go  \
      gzip \
      libc-dev \
      libtool  \
      libxslt-dev \
      linux-headers \
      libedit-dev \
      make \
      musl-dev \
      openssl-dev \
      pcre-dev \
      perl-dev \
      unzip \
      wget \
      zlib-dev \
  && apk add --no-cache --update \
      curl \
      wget \
      bash \
      bind-tools \
      rsync \
      geoip \
      openssl \
      pcre \
      tini \
      tar \
  && cd /tmp \
  && git clone https://github.com/google/ngx_brotli --depth=1 \
  && cd ngx_brotli && git submodule update --init \
  && export NGX_BROTLI_STATIC_MODULE_ONLY=1 \
  && cd /tmp \
  && git clone https://github.com/nbs-system/naxsi.git \
  && echo 'adding /usr/local/share/GeoIP/GeoIP.dat database' \
  && wget -N https://raw.githubusercontent.com/openbridge/nginx/master/geoip/GeoLiteCity.dat.gz \
  && wget -N https://raw.githubusercontent.com/openbridge/nginx/master/geoip/GeoIP.dat.gz \
  && gzip -d GeoIP.dat.gz \
  && gzip -d GeoLiteCity.dat.gz \
  && mkdir /usr/local/share/GeoIP/ \
  && mv GeoIP.dat /usr/local/share/GeoIP/ \
  && mv GeoLiteCity.dat /usr/local/share/GeoIP/ \
  && curl -fSL http://nginx.org/download/nginx-$NGINX_VERSION.tar.gz -o nginx.tar.gz \
  && mkdir -p /usr/src \
  && tar -zxC /usr/src -f nginx.tar.gz \
  && rm nginx.tar.gz \
  && cd /tmp \
  && git clone https://github.com/openresty/echo-nginx-module.git \
  && wget https://github.com/simpl/ngx_devel_kit/archive/v0.3.1.zip -O dev.zip \
  && wget https://github.com/openresty/set-misc-nginx-module/archive/v0.32.zip -O setmisc.zip \
  && wget https://people.freebsd.org/~osa/ngx_http_redis-0.3.9.tar.gz \
  && wget https://github.com/openresty/redis2-nginx-module/archive/v0.15.zip -O redis.zip \
  && wget https://github.com/openresty/srcache-nginx-module/archive/v0.31.zip -O cache.zip \
  && wget https://github.com/FRiCKLE/ngx_cache_purge/archive/2.3.zip -O purge.zip \
  && tar -zx -f ngx_http_redis-0.3.9.tar.gz \
  && unzip dev.zip \
   && unzip setmisc.zip \
   && unzip redis.zip \
   && unzip cache.zip \
   && unzip purge.zip \
   && cd /usr/src/nginx-$NGINX_VERSION \
   && ./configure $CONFIG --with-debug \
   && make -j$(getconf _NPROCESSORS_ONLN) \
   && mv objs/nginx objs/nginx-debug \
   && mv objs/ngx_http_xslt_filter_module.so objs/ngx_http_xslt_filter_module-debug.so \
  && mv objs/ngx_http_image_filter_module.so objs/ngx_http_image_filter_module-debug.so \
  && mv objs/ngx_stream_geoip_module.so objs/ngx_stream_geoip_module-debug.so \
  && ./configure $CONFIG \
  && make -j$(getconf _NPROCESSORS_ONLN) \
  && make install \
  && rm -rf /etc/nginx/html/ \
  && mkdir /etc/nginx/conf.d/ \
  && mkdir -p /usr/share/nginx/html/ \
  && install -m644 html/index.html /usr/share/nginx/html/ \
  && install -m644 html/50x.html /usr/share/nginx/html/ \
  && install -m755 objs/nginx-debug /usr/sbin/nginx-debug \
  && install -m755 objs/ngx_http_xslt_filter_module-debug.so /usr/lib/nginx/modules/ngx_http_xslt_filter_module-debug.so \
  && install -m755 objs/ngx_http_image_filter_module-debug.so /usr/lib/nginx/modules/ngx_http_image_filter_module-debug.so \
  && install -m755 objs/ngx_stream_geoip_module-debug.so /usr/lib/nginx/modules/ngx_stream_geoip_module-debug.so \
  && ln -s ../../usr/lib/nginx/modules /etc/nginx/modules \
  && strip /usr/sbin/nginx* \
  && strip /usr/lib/nginx/modules/*.so \
  && mkdir -p /usr/local/bin/ \
  && mkdir -p ${CACHE_PREFIX} \
  && mkdir -p ${CERTS_PREFIX} \
  && mv /usr/bin/envsubst /tmp/ \
  && runDeps="$( \
        scanelf --needed --nobanner /tmp/envsubst \
            | awk '{ gsub(/,/, "\nso:", $2); print "so:" $2 }' \
            | sort -u \
            | xargs -r apk info --installed \
            | sort -u \
    )" \
  && apk add --no-cache $runDeps \
  && mv /tmp/envsubst /usr/local/bin/ \
  && cd /etc/pki/tls/ \
  && apk add --no-cache $runDeps \
  && apk del .build-deps \
  && cd /tmp/naxsi \
  && mv naxsi_config/naxsi_core.rules /etc/nginx/naxsi_core.rules \
  && rm -rf /tmp/* \
  && rm -rf /usr/src/* \
  && ln -sf /dev/stdout ${LOG_PREFIX}/access.log \
  && ln -sf /dev/stderr ${LOG_PREFIX}/error.log \
  && ln -sf /dev/stdout ${LOG_PREFIX}/blocked.log

############################################################
# PHP
############################################################
RUN apk --no-cache add php8=${PHP_VERSION} \
      php8 \
      php8-ctype \
      php8-curl \
      php8-dom \
      php8-exif \
      php8-fileinfo \
      php8-fpm \
      php8-gd \
      php8-iconv \
      php8-intl \
      php8-mbstring \
      php8-mysqli \
      php8-opcache \
      php8-openssl \
      php8-pecl-imagick \
      php8-pecl-redis \
      php8-redis \
      php8-phar \
      php8-session \
      php8-simplexml \
      php8-soap \
      php8-xml \
      php8-xmlreader \
      php8-xmlwriter \
      php8-zip \
      php8-zlib \
      php8-bcmath \
      php8-pdo \
      php8-pdo_mysql \
      php8-pdo_pgsql \
      php8-pdo_sqlite \    
      php8-common \
      php8-tokenizer \
      php8-cli \
      php8-dev \
      php8-gettext \
      php8-json \
      php8-mysqlnd \
      php8-odbc \
      php8-posix \
      mysql-client\
      supervisor \
  && mkdir -p /var/run \
  && mkdir -p ${LOG_PREFIX} \
  && rm -rf /tmp/* \
  && rm -rf /var/cache/apk/* \
  && touch ${LOG_PREFIX}/access.log \
  && touch ${LOG_PREFIX}/error.log \
  && ln -sf /dev/stdout ${LOG_PREFIX}/access.log \
  && ln -sf /dev/stderr ${LOG_PREFIX}/error.log

# Symlink php8 => php
RUN ln -s /usr/bin/php8 /usr/bin/php

# Install PHP tools
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar && chmod +x wp-cli.phar && mv wp-cli.phar /usr/local/bin/wp
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && php composer-setup.php --install-dir=/usr/local/bin --filename=composer


RUN chown -R nobody.nobody /usr/share/nginx/html && \
  chown -R nobody.nobody /var/run && \
  chown -R nobody.nobody /run && \
  chown -R nobody.nobody /tmp && \
  chown -R nobody.nobody /var/cache && \
  chown -R nobody.nobody /etc/nginx && \
  chown -R nobody.nobody /etc/pki/tls && \
  chown -R nobody.nobody /etc/php8/

USER nobody

COPY --chown=nobody /docker/nginx/conf/ /conf
COPY --chown=nobody /docker/nginx/test/ /tmp/test
COPY --chown=nobody /docker/nginx/error/ /tmp/error/
COPY --chown=nobody /docker/nginx/nginx-entrypoint.sh /nginx-entrypoint.sh
COPY --chown=nobody /docker/nginx/php-entrypoint.sh /php-entrypoint.sh
############################################################
# COPY WORDPRESS
############################################################
COPY --chown=nobody:nobody /wp-app  /usr/share/nginx/html

RUN chmod +x /php-entrypoint.sh
RUN chmod +x /nginx-entrypoint.sh

# Configure supervisord
COPY /docker/nginx/conf/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Configure a healthcheck to validate that everything is up&running
HEALTHCHECK --timeout=10s CMD curl --silent --fail http://127.0.0.1:8080/fpm-ping


ENTRYPOINT ["/php-entrypoint.sh", "/nginx-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
STOPSIGNAL SIGQUIT
EXPOSE 8080
