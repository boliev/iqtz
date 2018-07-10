FROM php:7.1-fpm

WORKDIR /var/www/

RUN apt-get update && apt-get install

RUN apt-get install -y libpq-dev \
    && pecl install redis \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pdo pdo_pgsql pgsql bcmath \
    && docker-php-ext-enable redis \
    && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/*

ADD ./docker/php/wait-for-it.sh /wait-for-it.sh
ADD ./docker/php/bootstrap.sh /bootstrap.sh
ADD ./docker/php/create_data.php /create_data.php

RUN chmod +x /wait-for-it.sh
RUN chmod +x /bootstrap.sh

CMD ["/bootstrap.sh"]