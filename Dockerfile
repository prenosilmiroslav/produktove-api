FROM php:8.2-apache

RUN apt-get update && apt-get install -y libpq-dev git zip && docker-php-ext-install pdo pdo_pgsql pgsql && docker-php-ext-enable pdo_pgsql
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN a2enmod rewrite
RUN service apache2 restart

ENV APACHE_DOCUMENT_ROOT=/var/www/html/www
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY ./composer.json /var/www/html

WORKDIR /var/www/html

RUN composer install

