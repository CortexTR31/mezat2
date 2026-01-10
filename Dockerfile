FROM php:8.2-apache

# Apache MPM fix (ÇOK KRİTİK)
RUN a2dismod mpm_event \
    && a2enmod mpm_prefork

# Sistem paketleri
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# PHP MySQL driver'ları
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
