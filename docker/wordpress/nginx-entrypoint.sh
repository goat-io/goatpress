#!/usr/bin/env bash

#---------------------------------------------------------------------
# configure environment
#---------------------------------------------------------------------

function environment() {

# Set the ROOT directory for apps and content
  if [[ -z ${NGINX_DOCROOT} ]]; then 
    NGINX_DOCROOT=/usr/share/nginx/html && export NGINX_DOCROOT && mkdir -p "${NGINX_DOCROOT}"; 
  fi

  if [[ -z ${PHP_FPM_UPSTREAM} ]]; then 
    PHP_FPM_UPSTREAM="localhost:9000" && export PHP_FPM_UPSTREAM;  
  fi

  if [[ -z ${NGINX_PROXY_UPSTREAM} ]]; then
    NGINX_PROXY_UPSTREAM="localhost:8081" && export NGINX_PROXY_UPSTREAM; 
  fi

  if [[ -z ${NGINX_REDIS_UPSTREAM} ]]; then 
    NGINX_REDIS_UPSTREAM="127.0.0.1:6379" && export NGINX_REDIS_UPSTREAM; 
  fi

}

#---------------------------------------------------------------------
# set variables
#---------------------------------------------------------------------

function config() {

# Copy the configs to the main nginx directories
 rsync -av --ignore-missing-args /conf/nginx/* ${CONF_PREFIX}/
     
      PAGESPEED_BEACON=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)

      # Set the ENV variables in all configs
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_DOCROOT}}|'"${NGINX_DOCROOT}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{CACHE_PREFIX}}|'"${CACHE_PREFIX}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_SERVER_NAME}}|'"${NGINX_SERVER_NAME}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{LOG_PREFIX}}|'"${LOG_PREFIX}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{PAGESPEED_BEACON}}|'"${PAGESPEED_BEACON}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_CDN_HOST}}|'"${NGINX_CDN_HOST}"'|g' {} \;

      # Replace Upstream servers
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{PHP_FPM_UPSTREAM}}|'"${PHP_FPM_UPSTREAM}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_PROXY_UPSTREAM}}|'"${NGINX_PROXY_UPSTREAM}"'|g' {} \;
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_REDIS_UPSTREAM}}|'"${NGINX_REDIS_UPSTREAM}"'|g' {} \;

      # Replace SPA
      find "${CONF_PREFIX}" -maxdepth 5 -type f -exec sed -i -e 's|{{NGINX_SPA_PRERENDER}}|'"${NGINX_SPA_PRERENDER}"'|g' {} \;

}

#---------------------------------------------------------------------
# set pernissions for nobody
#---------------------------------------------------------------------

function permissions() {

  find ${NGINX_DOCROOT} ! -user nobody -exec /usr/bin/env bash -c 'i="$1"; chown nobody:nobody "$i"' _ {} \;
  find ${NGINX_DOCROOT} ! -perm 755 -type d -exec /usr/bin/env bash -c 'i="$1"; chmod 755  "$i"' _ {} \;
  find ${NGINX_DOCROOT} ! -perm 644 -type f -exec /usr/bin/env bash -c 'i="$1"; chmod 644 "$i"' _ {} \;
  find ${CACHE_PREFIX} ! -perm 755 -type d -exec /usr/bin/env bash -c 'i="$1"; chmod 755  "$i"' _ {} \;
  find ${CACHE_PREFIX} ! -perm 644 -type f -exec /usr/bin/env bash -c 'i="$1"; chmod 644 "$i"' _ {} \;

}

#---------------------------------------------------------------------
# configure SSL
#---------------------------------------------------------------------

function openssl() {

  # The first argument is the bit depth of the dhparam, or 2048 if unspecified
  DHPARAM_BITS=${1:-2048}

  # If a dhparam file is not available, use the pre-generated one and generate a new one in the background.
  PREGEN_DHPARAM_FILE=${CERTS_PREFIX}/dhparam.pem.default
  DHPARAM_FILE=${CERTS_PREFIX}/dhparam.pem
  GEN_LOCKFILE=/tmp/dhparam_generating.lock

  if [[ ! -f ${PREGEN_DHPARAM_FILE} ]]; then
     echo "OK: NO PREGEN_DHPARAM_FILE is present. Generate ${PREGEN_DHPARAM_FILE}..."
     nice -n +5 openssl dhparam -out ${DHPARAM_FILE} 2048 2>&1
  fi

  if [[ ! -f ${DHPARAM_FILE} ]]; then
     # Put the default dhparam file in place so we can start immediately
     echo "OK: NO DHPARAM_FILE present. Copy ${PREGEN_DHPARAM_FILE} to ${DHPARAM_FILE}..."
     cp ${PREGEN_DHPARAM_FILE} ${DHPARAM_FILE}
     touch ${GEN_LOCKFILE}

     # The hash of the pregenerated dhparam file is used to check if the pregen dhparam is already in use
     PREGEN_HASH=$(md5sum ${PREGEN_DHPARAM_FILE} | cut -d" " -f1)
     CURRENT_HASH=$(md5sum ${DHPARAM_FILE} | cut -d" " -f1)
     if [[ "${PREGEN_HASH}" != "${CURRENT_HASH}" ]]; then
      # Generate a new dhparam in the background in a low priority and reload nginx when finished (grep removes the progress indicator).
     (
         (
             nice -n +5 openssl dhparam -out ${DHPARAM_FILE} ${DHPARAM_BITS} 2>&1 \
         ) | grep -vE '^[\.+]+'
         rm ${GEN_LOCKFILE}
     ) &disown
    fi
  fi

  # Add Let's Encrypt CA in case it is needed
  mkdir -p /etc/ssl/private
  cd /etc/ssl/private || exit
  wget -O - https://letsencrypt.org/certs/isrgrootx1.pem https://letsencrypt.org/certs/lets-encrypt-x1-cross-signed.pem https://letsencrypt.org/certs/letsencryptauthorityx1.pem https://www.identrust.com/certificates/trustid/root-download-x3.html | tee -a ca-certs.pem> /dev/null

}

#---------------------------------------------------------------------
# install CDN
#---------------------------------------------------------------------

function cdn () {
  {
     echo 'location ~* \.(gif|png|jpg|jpeg|svg|gif/|png/|jpg/|jpeg/|svg/)$ {'
    echo '   return  301 https://{{NGINX_CDN_HOST}}$request_uri;'
		echo '}'
    } | tee /etc/nginx/conf.d/cdn.conf
}


function redis() {
  echo 'SETTING UP REDIS'
  if [[ -z ${NGINX_REDIS_PASSWORD} ]]; then
    {
      echo 'location = /redis_get {'
      echo '   internal;'
      echo '   set_md5                         $redis_key $args;'
      echo '   redis_pass                      redis;'
		  echo '}'
      echo ''
      echo ''
      echo ''
      echo 'location = /redis_put {'
      echo '   internal;'
      echo '   set_unescape_uri                $exptime $arg_exptime;'
      echo '   set_unescape_uri                $key $arg_key;'
      echo '   set_md5                         $key;'
      echo '   redis2_query set                $key $echo_request_body;'
      echo '   redis2_query expire             $key $exptime;'
      echo '   redis2_pass                     redis;'
		  echo '}'
    } | tee /etc/nginx/redis.d/location.conf
  else
    {
        echo 'location = /redis_get {'
        echo '   internal;'
        echo "   set \$redis_auth ${NGINX_REDIS_PASSWORD};"
        echo "   redis2_query auth ${NGINX_REDIS_PASSWORD};"
        echo '   set_md5                         $redis_key $args;'
        echo '   redis_pass                      redis;'
        echo '}'
        echo ''
        echo ''
        echo ''
        echo 'location = /redis_put {'
        echo '   internal;'
        echo "   set \$redis_auth ${NGINX_REDIS_PASSWORD};"
        echo "   redis2_query auth ${NGINX_REDIS_PASSWORD};"
        echo '   set_unescape_uri                $exptime $arg_exptime;'
        echo '   set_unescape_uri                $key $arg_key;'
        echo '   set_md5                         $key;'
        echo '   redis2_query set                $key $echo_request_body;'
        echo '   redis2_query expire             $key $exptime;'
        echo '   redis2_pass                     redis;'
        echo '}'
      } | tee /etc/nginx/redis.d/location.conf

  fi

}
#---------------------------------------------------------------------
# start everything up
#---------------------------------------------------------------------

function run() {
   environment
   #openssl
   if [[ -z ${NGINX_CDN_HOST} ]]; then 
   echo "CDN was not set"; 
   else 
      echo "SETTING UP CDN"
      cdn; 
   fi
   config

   if [[ -z ${WP_REDIS_DISABLED} ]]; then 
      echo "SETTING UP REDIS"
      redis;
   else 
      echo "REDIS was disabled"; 
   fi
}

run

exec "$@"