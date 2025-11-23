FROM php:8.1-apache

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite
RUN a2enmod rewrite

# Crea directory per plugin e uploads
RUN mkdir -p /var/www/html/uploads /var/www/html/plugins && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

WORKDIR /var/www/html

# Copia file applicazione
COPY . /var/www/html/

# Imposta permessi
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

