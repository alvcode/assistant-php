FROM dunglas/frankenphp:1-php8.4

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
      pecl install apcu && \
      docker-php-ext-enable apcu && \
      apt-get remove -y gcc make autoconf && \
      apt-get autoremove -y && \
      apt-get clean && \
      rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./_docker/dev/app/frankenphp.ini /usr/local/etc/php/conf.d/99-custom.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY ./_docker/dev/app/entrypoint.sh /docker-entrypoint.d/entrypoint.sh
RUN chmod +x /docker-entrypoint.d/entrypoint.sh

CMD ["/docker-entrypoint.d/entrypoint.sh"]
