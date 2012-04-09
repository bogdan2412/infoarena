#!/bin/bash

source $(dirname $(readlink -f $0))/hphp-set-env-var

mkdir -p $PREFIX
cd $PREFIX

sudo apt-get install git
# sudo yum install git
git clone git://github.com/facebook/hiphop-php.git

mkdir $CMAKE_PREFIX_PATH
# Custom libevent library
wget http://www.monkey.org/~provos/libevent-1.4.14b-stable.tar.gz
tar -xvf libevent-1.4.14b-stable.tar.gz && rm libevent-1.4.14b-stable.tar.gz
cd libevent-1.4.14b-stable
cp ../hiphop-php/src/third_party/libevent-1.4.14.fb-changes.diff .
patch -p1 < libevent-1.4.14.fb-changes.diff
./configure --prefix=$CMAKE_PREFIX_PATH &&
make &&
make install
if [ $? -ne 0 ]; then
    exit;
fi
cd ..

# Custom curl library
wget http://curl.haxx.se/download/curl-7.21.2.tar.bz2
tar -xvf curl-7.21.2.tar.bz2 && rm curl-7.21.2.tar.bz2
cd curl-7.21.2
cp ../hiphop-php/src/third_party/libcurl.fb-changes.diff .
patch -p1 < libcurl.fb-changes.diff
./configure --prefix=$CMAKE_PREFIX_PATH &&
make &&
make install
if [ $? -ne 0 ]; then
    exit;
fi
cd ..

# Other dependencies
sudo apt-get install -y cmake \
                        libboost-dev \
                        libboost-system-dev \
                        libboost-program-options-dev \
                        libboost-filesystem-dev
                        libmysqlclient-dev \
                        libmemcached-dev \
                        libpcre3-dev \
                        libgd2-xpm-dev \
                        libxml2-dev \
                        libicu-dev \
                        libtbb-dev \
                        libmcrypt-dev \
                        libbz2-dev \
                        libonig-dev \
                        libldap2-dev \
                        libreadline-dev \
                        libc-client2007e-dev \
                        libcap-dev \
                        binutils-dev \
                        libzip-dev
# sudo yum install boost-devel flex bison re2c mysql-devel libxml2-devel
# libmcrypt-devel libicu-devel openssl-devel binutils-devel libcap-devel
# gd-devel zlib-devel tbb-devel pcre-devel expat-devel libmemcached-devel
# bzip2-devel readline-devel openldap-devel oniguruma-devel uw-imap-devel
# pam-devel

# Build HPHP
cd hiphop-php
cmake . && make -j3
