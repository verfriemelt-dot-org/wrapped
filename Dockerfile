FROM php:8.0-cli-buster
RUN apt-get update && \
    apt-get install -y unzip fd-find entr

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /app
WORKDIR /app
RUN composer install

CMD php vendor/bin/codecept run unit
