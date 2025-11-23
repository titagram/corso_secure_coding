FROM php:8.1-fpm

# Installa le dipendenze di sistema necessarie
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    iputils-ping \
    && rm -rf /var/lib/apt/lists/*

# Installa le estensioni PHP necessarie per MySQL
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Configura PHP-FPM per accettare connessioni da Apache
RUN sed -i 's/listen = 127.0.0.1:9000/listen = 9000/g' /usr/local/etc/php-fpm.d/www.conf

# Imposta la directory di lavoro
WORKDIR /var/www/html

# Esponi la porta 9000 per PHP-FPM
EXPOSE 9000

CMD ["php-fpm"]
