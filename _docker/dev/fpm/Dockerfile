FROM php:8.4.21-fpm

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
      apt-utils \
      libpq-dev \
      libpng-dev \
      libzip-dev \
      libonig-dev \
      libicu-dev \
      zip unzip \
      autoconf \
      gcc \
      make \
      procps && \
      docker-php-ext-install pdo_pgsql && \
      docker-php-ext-install bcmath && \
      docker-php-ext-install gd && \
      docker-php-ext-install zip && \
      docker-php-ext-install mbstring && \
      docker-php-ext-install sockets && \
      docker-php-ext-configure intl && \
      docker-php-ext-install intl && \
      docker-php-ext-install opcache && \
      apt-get remove -y gcc make autoconf && \
      apt-get autoremove -y && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./_docker/dev/fpm/php.ini /usr/local/etc/php/conf.d/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN echo "zend_extension=$(php-config --extension-dir)/opcache.so" >> /usr/local/etc/php/conf.d/xdebug.ini
COPY ./_docker/dev/fpm/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www
