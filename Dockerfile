FROM php:8.2-apache

# PHP MySQL driver'ları (ZORUNLU)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
