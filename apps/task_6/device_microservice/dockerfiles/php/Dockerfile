FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    curl

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install \
    pdo_pgsql

WORKDIR /var/www

CMD ["php-fpm"]

EXPOSE 9000