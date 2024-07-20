FROM php:8.2.0-fpm

RUN apt-get update
RUN apt-get install -y git

WORKDIR /root

RUN git clone -b 4.0.10 https://github.com/cyjoelchen/php-sweph.git

WORKDIR /root/php-sweph
RUN ./build-se