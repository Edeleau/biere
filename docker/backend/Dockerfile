FROM php:7.4-fpm

COPY --from=composer:2.0.11 /usr/bin/composer /usr/bin/composer

RUN apt-get update &&\
 apt-get install -y libicu-dev zip unzip libpng-dev libfreetype6-dev libjpeg62-turbo-dev libzip-dev \
 libxml2-dev libxslt-dev git vim python3-pip

RUN pip3 install ansible

ADD https://dl.google.com/linux/direct/google-chrome-stable_current_amd64.deb ./google-chrome-stable_current_amd64.deb
RUN apt-get install -f -y ./google-chrome-stable_current_amd64.deb
RUN rm google-chrome-stable_current_amd64.deb

ENV PANTHER_NO_SANDBOX=1
ENV PANTHER_CHROME_ARGUMENTS='--disable-dev-shm-usage'

RUN docker-php-ext-configure gd
RUN docker-php-ext-configure zip
RUN docker-php-ext-install pdo_mysql intl gd zip exif gettext json

RUN pecl install apcu-5.1.19
RUN pecl clear-cache

RUN docker-php-ext-enable opcache apcu

COPY php.ini /usr/local/etc/php/php.ini

ENV PATH="/var/www/html/vendor/bin:${PATH}"

ARG UID=1000
ARG GID=1000

RUN getent group ${GID} || addgroup --gid ${GID} beer

RUN useradd --uid ${UID} --gid ${GID} beer
USER beer
