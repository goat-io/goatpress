#!/usr/bin/env bash

#---------------------------------------------------------------------
# install bad bot protection
#---------------------------------------------------------------------

function bots() {
    # https://github.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker
    mkdir -p /etc/nginx/sites-available
    # Change the install direcotry:
    cd /usr/sbin || exit
    # Download the config, install and update applications
    wget https://raw.githubusercontent.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker/master/install-ngxblocker -O install-ngxblocker
    wget https://raw.githubusercontent.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker/master/setup-ngxblocker -O setup-ngxblocker
    wget https://raw.githubusercontent.com/mitchellkrogza/nginx-ultimate-bad-bot-blocker/master/update-ngxblocker -O update-ngxblocker
    # Set permissions
    chmod +x install-ngxblocker
    chmod +x setup-ngxblocker
    chmod +x update-ngxblocker
    # Run installer and configuration
    install-ngxblocker -x
    setup-ngxblocker -x -w ${NGINX_DOCROOT}
    echo "OK: Clean up variables..."
    sed -i -e 's|^variables_hash_max_|#variables_hash_max_|g' /etc/nginx/conf.d/botblocker-nginx-settings.conf
}


function run() {
   bots
}

run

exec "$@"