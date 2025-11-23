FROM php:8.1-apache

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Abilita mod_rewrite
RUN a2enmod rewrite

# Crea directory per log e vault
RUN mkdir -p /var/www/html/logs /var/www/html/vault && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/logs

WORKDIR /var/www/html

# Copia file applicazione
COPY . /var/www/html/

# Imposta permessi (VULNERABILITÀ: permessi troppo permissivi per log)
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/logs

EXPOSE 80

