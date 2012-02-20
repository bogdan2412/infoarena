#!/bin/bash

if [ "$(id -u)" != "0" ]; then
    echo "You are not root, trying to run through sudo"
    sudo $0 $*
    exit
fi

/etc/init.d/apache2 start
/etc/init.d/infoarena start
/etc/init.d/nginx start
