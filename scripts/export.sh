#!/bin/bash
_os="`uname`"
_now=$(date +"%m_%d_%Y_%H:%M")
_file="wp-data/data_$_now.sql"
docker-compose exec db sh -c 'exec mysqldump "$MYSQL_DATABASE" -uroot -p"$MYSQL_ROOT_PASSWORD"' > $_file