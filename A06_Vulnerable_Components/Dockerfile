# VULNERABILITÀ: PHP 7.4 obsoleto (EOL dal 28 Nov 2022)
# PHP 7.4 contiene vulnerabilità note e non riceve più aggiornamenti di sicurezza
FROM php:7.4-apache

# Abilita mod_rewrite per Apache
RUN a2enmod rewrite

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# VULNERABILITÀ: Abilita display_errors in produzione
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/errors.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/errors.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/errors.ini

# VULNERABILITÀ: Espone informazioni di versione PHP
RUN echo "expose_php = On" >> /usr/local/etc/php/conf.d/errors.ini

# Configura la directory di lavoro
WORKDIR /var/www/html

# Copia i file dell'applicazione
COPY . /var/www/html/

# Imposta i permessi corretti
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Espone la porta 80
EXPOSE 80

