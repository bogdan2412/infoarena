FROM php:5-fpm

MAINTAINER Adrian Budau

WORKDIR /infoarena

RUN apt-get update && apt-get install -y vim libmcrypt-dev\
    libcurl4-openssl-dev mysql-client libpng-dev git openjdk-7-jdk\
    && docker-php-ext-install -j$(nproc) iconv mcrypt mysql curl gd zip mysqli
