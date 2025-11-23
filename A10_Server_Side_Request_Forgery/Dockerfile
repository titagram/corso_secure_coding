FROM php:8.1-apache

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Installa curl e altre dipendenze
RUN apt-get update && apt-get install -y \
    curl \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Abilita mod_rewrite
RUN a2enmod rewrite

WORKDIR /var/www/html

# Copia file applicazione
COPY . /var/www/html/

# Imposta permessi
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

