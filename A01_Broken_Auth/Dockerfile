FROM php:8.1-apache

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Configura la directory di lavoro
WORKDIR /var/www/html

# Copia i file dell'applicazione
COPY . /var/www/html/

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Espone la porta 80
EXPOSE 80




