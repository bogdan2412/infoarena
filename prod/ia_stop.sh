#!/bin/bash

if [ "$(id -u)" != "0" ]; then
    echo "You are not root, trying to run through sudo"
    sudo $0 $*
    exit
fi

/etc/init.d/nginx stop
/etc/init.d/infoarena stop
/etc/init.d/apache2 stop
