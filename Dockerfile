FROM php:8.2-cli

# MySQL PDO driver kur
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Çalışma dizini
WORKDIR /app

# Projeyi kopyala
COPY . .

# Railway port
EXPOSE 8080

# PHP server başlat
CMD ["php", "-S", "0.0.0.0:8080"]
