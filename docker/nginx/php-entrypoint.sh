#!/usr/bin/env bash

#---------------------------------------------------------------------
# configure php-fpm
#---------------------------------------------------------------------

function php-fpm() {

  # Determine the PHP-FPM runtime environment
   CPU=$(grep -c ^processor /proc/cpuinfo); echo "${CPU}"
   TOTALMEM=$(free -m | awk '/^Mem:/{print $2}'); echo "${TOTALMEM}"

   if [[ "$CPU" -le "2" ]]; then TOTALCPU=2; else TOTALCPU="${CPU}"; fi

   # PHP-FPM settings
   if [[ -z $PHP_START_SERVERS ]]; then PHP_START_SERVERS=$(($TOTALCPU / 2)) && echo "${PHP_START_SERVERS}"; fi
   if [[ -z $PHP_MIN_SPARE_SERVERS ]]; then PHP_MIN_SPARE_SERVERS=$(($TOTALCPU / 2)) && echo "${PHP_MIN_SPARE_SERVERS}"; fi
   if [[ -z $PHP_MAX_SPARE_SERVERS ]]; then PHP_MAX_SPARE_SERVERS="${TOTALCPU}" && echo "${PHP_MAX_SPARE_SERVERS}"; fi
   if [[ -z $PHP_MEMORY_LIMIT ]]; then PHP_MEMORY_LIMIT=$(($TOTALMEM / 2)) && echo "${PHP_MEMORY_LIMIT}"; fi
   if [[ -z $PHP_MAX_CHILDREN ]]; then PHP_MAX_CHILDREN=$(($TOTALCPU * 2)) && echo "${PHP_MAX_CHILDREN}"; fi
   if [[ -z $PHP_POST_MAX_SIZE ]]; then PHP_POST_MAX_SIZE="50"; else PHP_POST_MAX_SIZE="${PHP_POST_MAX_SIZE}"; fi
   if [[ -z $PHP_UPLOAD_MAX_FILESIZE ]]; then PHP_UPLOAD_MAX_FILESIZE="50"; else PHP_UPLOAD_MAX_FILESIZE="${PHP_UPLOAD_MAX_FILESIZE}"; fi
   if [[ -z $PHP_MAX_INPUT_VARS ]]; then PHP_MAX_INPUT_VARS="1000"; else PHP_MAX_INPUT_VARS="${PHP_MAX_INPUT_VARS}"; fi
   if [[ -z $PHP_MAX_EXECUTION_TIME ]]; then PHP_MAX_EXECUTION_TIME="300"; else PHP_MAX_EXECUTION_TIME="${PHP_MAX_EXECUTION_TIME}"; fi
   
   # Opcache settings
   if [[ -z $PHP_OPCACHE_ENABLE ]]; then PHP_OPCACHE_ENABLE=1 && echo "${PHP_OPCACHE_ENABLE}"; fi
   if [[ -z $PHP_OPCACHE_MEMORY_CONSUMPTION ]]; then PHP_OPCACHE_MEMORY_CONSUMPTION=$(($TOTALMEM / 6)) && echo "${PHP_OPCACHE_MEMORY_CONSUMPTION}"; fi

   # Set the listening port
   if [[ -z $PHP_FPM_PORT ]]; then echo "PHP-FPM port not set. Default to 9000..." && export PHP_FPM_PORT=9000; else echo "OK, PHP-FPM port is set to $PHP_FPM_PORT"; fi
   # Set the document root. This is usually the same as your NGINX docroot
   if [[ -z $APP_DOCROOT ]]; then export APP_DOCROOT=/app && mkdir -p "${APP_DOCROOT}"; fi

  {
        echo '[global]'
        echo 'include=/etc/php8/php-fpm.d/*.conf'
  } | tee /etc/php8/php-fpm.conf

  {
        echo '[global]'
        echo 'error_log = /dev/stderr'
        echo
        echo '[www]'
        echo '; if we send this to /proc/self/fd/1, it never appears'
        echo 'access.log = {{LOG_PREFIX}}/access.log'
        echo
        echo 'clear_env = no'
        echo '; ping.path = /ping'
        echo '; Ensure worker stdout and stderr are sent to the main error log.'
        echo 'catch_workers_output = yes'
        echo 'decorate_workers_output = no'
        echo 'ping.path = /fpm-ping'
        echo 'pm.status_path = /fpm-status'
        echo 'pm = ondemand'
        echo 'pm.process_idle_timeout = 10s;'
  } | tee /etc/php8/php-fpm.d/docker.conf

  {
        echo '[global]'
        echo 'daemonize = no'
        echo 'log_level = error'
        echo
        echo '[www]'
        echo 'user = nobody'
        echo 'group = nobody'
        echo 'listen = [::]:{{PHP_FPM_PORT}}'
        echo 'listen.mode = 0666'
        echo 'listen.owner = nobody'
        echo 'listen.group = nobody'
        echo 'pm = static'
        echo 'pm.max_children = {{PHP_MAX_CHILDREN}}'
        echo 'pm.max_requests = {{PHP_MAX_REQUESTS}}'
        echo 'pm.start_servers = {{PHP_START_SERVERS}}'
        echo 'pm.min_spare_servers = {{PHP_MIN_SPARE_SERVERS}}'
        echo 'pm.max_spare_servers = {{PHP_MAX_SPARE_SERVERS}}'
  } | tee /etc/php8/php-fpm.d/zz-docker.conf

  {
        echo 'max_execution_time={{PHP_MAX_EXECUTION_TIME}}'
        echo 'memory_limit={{PHP_MEMORY_LIMIT}}M'
        echo 'error_reporting=1'
        echo 'display_errors=0'
        echo 'log_errors=1'
        echo 'user_ini.filename='
        echo 'realpath_cache_size=2M'
        echo 'cgi.check_shebang_line=0'
        echo 'date.timezone=UTC'
        echo 'short_open_tag=Off'
        echo 'session.auto_start=Off'
        echo 'upload_max_filesize={{PHP_UPLOAD_MAX_FILESIZE}}M'
        echo 'post_max_size={{PHP_POST_MAX_SIZE}}M'
        echo 'file_uploads=On'
        echo 'max_input_vars={{PHP_MAX_INPUT_VARS}}'
        echo 'max_input_time={{PHP_MAX_INPUT_TIME}}'

        echo
        echo 'opcache.enable={{PHP_OPCACHE_ENABLE}}'
        echo 'opcache.enable_cli=0'
        echo 'opcache.save_comments=1'
        echo 'opcache.interned_strings_buffer=8'
        echo 'opcache.fast_shutdown=1'
        echo 'opcache.validate_timestamps=2'
        echo 'opcache.revalidate_freq=0'
        echo 'opcache.use_cwd=1'
        echo 'opcache.max_accelerated_files=100000'
        echo 'opcache.max_wasted_percentage=5'
        echo 'opcache.memory_consumption={{PHP_OPCACHE_MEMORY_CONSUMPTION}}M'
        echo 'opcache.consistency_checks=0'
        echo 'opcache.huge_code_pages=1'
        echo 'opcache.jit_buffer_size=64M'
        echo 'opcache.jit=tracing'
        echo
        echo ';opcache.file_cache="{{CACHE_PREFIX}}/fastcgi/.opcache"'
        echo ';opcache.file_cache_only=1'
        echo ';opcache.file_cache_consistency_checks=1'
  } | tee /etc/php8/conf.d/50-setting.ini

  mkdir -p "${CACHE_PREFIX}"/fastcgi/

# Set the configs with the ENV Var
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{CACHE_PREFIX}}|'"${CACHE_PREFIX}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_FPM_PORT}}|'"${PHP_FPM_PORT}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_START_SERVERS}}|'"${PHP_START_SERVERS}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MIN_SPARE_SERVERS}}|'"${PHP_MIN_SPARE_SERVERS}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MAX_SPARE_SERVERS}}|'"${PHP_MAX_SPARE_SERVERS}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MEMORY_LIMIT}}|'"${PHP_MEMORY_LIMIT}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_OPCACHE_ENABLE}}|'"${PHP_OPCACHE_ENABLE}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_OPCACHE_MEMORY_CONSUMPTION}}|'"${PHP_OPCACHE_MEMORY_CONSUMPTION}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MAX_CHILDREN}}|'"${PHP_MAX_CHILDREN}"'|g' {} \;

  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MAX_REQUESTS}}|'"${PHP_MAX_REQUESTS}"'|g' {} \;

  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{LOG_PREFIX}}|'"${LOG_PREFIX}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_POST_MAX_SIZE}}|'"${PHP_POST_MAX_SIZE}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_UPLOAD_MAX_FILESIZE}}|'"${PHP_UPLOAD_MAX_FILESIZE}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MAX_INPUT_VARS}}|'"${PHP_MAX_INPUT_VARS}"'|g' {} \;
  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{PHP_MAX_EXECUTION_TIME}}|'"${PHP_MAX_EXECUTION_TIME}"'|g' {} \;

}

#---------------------------------------------------------------------
# configure php connection to redis
#---------------------------------------------------------------------

function redis() {

{
      echo 'session.gc_maxlifetime=86400'
      echo 'session.save_handler=redis'
      echo 'session.save_path="tcp://{{REDIS_UPSTREAM}}?weight=1&timeout=2.5&database=3"'
} | tee /etc/php8/conf.d/zz-redis-setting.ini

  find /etc/php8 -maxdepth 3 -type f -exec sed -i -e 's|{{REDIS_UPSTREAM}}|'"${REDIS_UPSTREAM}"'|g' {} \;

}

#---------------------------------------------------------------------
# configure the use of a plugin (install script) for wordpress or similiar
#---------------------------------------------------------------------

function install_plugin() {

if [[ ! -d /usr/src/plugins/$NGINX_APP_PLUGIN ]]; then
  echo "INFO: NGINX_APP_PLUGIN is not located in the plugin directory. Nothing to install..."
else
  echo "OK: Installing NGINX_APP_PLUGIN=$NGINX_APP_PLUGIN..."
  #Give other services a chance to start up...
  sleep 10
  chmod +x /usr/src/plugins/$NGINX_APP_PLUGIN/install
  runplugin="/usr/src/plugins/$NGINX_APP_PLUGIN/install" && /bin/bash -c "${runplugin}"
fi

}

#---------------------------------------------------------------------
# make sure all permissions are set correctly for php-fpm
#---------------------------------------------------------------------

function permissions() {

    echo "Setting ownership and permissions on APP_ROOT and CACHE_PREFIX... "

    # This assumes you are using the common nobody for your user and group in NGINX and PHP-FPM. If you are using different users this is usually a recipe for error.

    find ${APP_DOCROOT} ! -user nobody -exec /usr/bin/env bash -c 'i="$1"; chown nobody:nobody "$i"' _ {} \;
    find ${APP_DOCROOT} ! -perm 755 -type d -exec /usr/bin/env bash -c 'i="$1"; chmod 755  "$i"' _ {} \;
    find ${APP_DOCROOT} ! -perm 644 -type f -exec /usr/bin/env bash -c 'i="$1"; chmod 644 "$i"' _ {} \;
    find ${CACHE_PREFIX} ! -perm 755 -type d -exec /usr/bin/env bash -c 'i="$1"; chmod 755  "$i"' _ {} \;
    find ${CACHE_PREFIX} ! -perm 644 -type f -exec /usr/bin/env bash -c 'i="$1"; chmod 644 "$i"' _ {} \;

}

#---------------------------------------------------------------------
# run all the functions to start the services
#---------------------------------------------------------------------

function run() {

  php-fpm
  if [[ -z $REDIS_UPSTREAM ]]; then echo "OK: Redis is not present so we will not activate it"; else redis; fi
  if [[ ! -z $NGINX_APP_PLUGIN ]]; then install_plugin; else echo "OK: No plugins will be activated"; fi
}

run

exec "$@"