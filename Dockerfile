FROM php:7.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    supervisor \
    libicu-dev \
    librabbitmq-dev \
    libssh-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN docker-php-ext-install intl pcntl

RUN pecl install amqp && docker-php-ext-enable amqp

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

ENTRYPOINT supervisord -c /etc/supervisor/supervisord.conf && php-fpm -R -F
