FROM php:8.2-apache

# Sistem paketleri
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# PHP MySQL driver'ları (ÇOK KRİTİK)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache rewrite
RUN a2enmod rewrite

# Çalışma dizini
WORKDIR /var/www/html

# Proje dosyaları
COPY . /var/www/html

# İzinler
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
