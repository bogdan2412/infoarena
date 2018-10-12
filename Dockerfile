FROM php:5-fpm

MAINTAINER Adrian Budau

WORKDIR /infoarena

RUN mkdir -p /usr/share/man/man1
RUN echo "deb http://http.debian.net/debian jessie-backports main" >\
    /etc/apt/sources.list.d/backports.list
RUN apt-get update && apt-get install -y vim libmcrypt-dev\
    libcurl4-openssl-dev mysql-client libpng-dev git man openjdk-8-jdk\
    gcc-multilib g++-multilib fpc && docker-php-ext-install -j$(nproc)\
    iconv mcrypt mysql curl gd zip mysqli

RUN curl https://sh.rustup.rs -sSf | sh -s -- -y
RUN echo 'export PATH="$HOME/.cargo/bin:$PATH"' > $HOME/.bashrc
RUN /root/.cargo/bin/cargo install ia-sandbox
