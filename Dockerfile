FROM php:8.2-apache

# --- APACHE MPM TAM RESET (ÇOK KRİTİK) ---
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load \
          /etc/apache2/mods-enabled/mpm_worker.load \
          /etc/apache2/mods-enabled/mpm_prefork.load \
    && a2enmod mpm_prefork

# PHP MySQL driver'ları
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Apache mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
