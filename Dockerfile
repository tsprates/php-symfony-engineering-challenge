FROM php:7.4-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    zip \
    supervisor \
    libicu-dev \ 
    librabbitmq-dev \
    libssh-dev

RUN docker-php-ext-install intl

RUN docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pcntl

RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN pecl install amqp \
    && docker-php-ext-enable amqp

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

ENTRYPOINT supervisord -c /etc/supervisor/supervisord.conf && php-fpm -R -F