#!/bin/bash

if [ "$(id -u)" != "0" ]; then
    echo "You are not root, trying to run through sudo"
    sudo $0 $*
    exit
fi

$(dirname $0)/ia_stop.sh
/etc/init.d/mysql stop
/etc/init.d/memcached stop
/bin/sync
echo 3 > /proc/sys/vm/drop_caches
/sbin/swapoff -a
sleep 2
/sbin/swapon -a
/etc/init.d/memcached start
/etc/init.d/mysql start
$(dirname $0)/ia_start.sh
