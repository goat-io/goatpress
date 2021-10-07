#!/bin/bash
echo "CHECKING LOCAL MYSQL"
check=$(curl http://127.0.0.1:3306 --output - 2>&1 | grep -o 8.0)

while [ -z "$check" ]; do
    # wait a moment
    #
    echo "MYSQL is not ready, waiting 5"
    sleep 5s

    # check again
    #
    check=$(curl http://127.0.0.1:3306 --output - 2>&1 | grep -o 8.0)
done

echo "MYSQL up and running"