FROM php:8.2.7-fpm

ADD php.ini /usr/local/etc/php/conf.d/40-custom.ini
WORKDIR /var/www

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN apt-get update && apt-get -y install ca-certificates
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g $HOST_GID www
RUN useradd -u $HOST_UID -ms /bin/bash -g www www

USER www
CMD ["php-fpm"]