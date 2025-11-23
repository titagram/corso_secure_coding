FROM php:8.1-apache

# Abilita mod_rewrite e autoindex per Apache
RUN a2enmod rewrite && a2enmod autoindex

# Installa estensioni PHP necessarie
RUN docker-php-ext-install mysqli pdo pdo_mysql

# VULNERABILITÀ: Abilita display_errors in produzione (dovrebbe essere disabilitato!)
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/errors.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/errors.ini
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/errors.ini

# Configura la directory di lavoro
WORKDIR /var/www/html

# Copia i file dell'applicazione
COPY . /var/www/html/

# Crea directory necessarie (se non esistono già)
RUN mkdir -p /var/www/html/uploads /var/www/html/backups /var/www/html/logs /var/www/html/admin

# VULNERABILITÀ: Permessi troppo permissivi
# Imposta permessi espliciti per le directory
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod 755 /var/www/html/uploads /var/www/html/backups /var/www/html/logs /var/www/html/admin

# VULNERABILITÀ: Abilita directory listing modificando la configurazione Apache di default
# php:8.1-apache usa la configurazione in /etc/apache2/sites-available/000-default.conf
# Assicurati che AllowOverride sia All per permettere .htaccess
RUN sed -i 's/Options -Indexes +FollowSymLinks/Options Indexes FollowSymLinks/g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/Options -Indexes/Options Indexes FollowSymLinks/g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/sites-available/000-default.conf

# VULNERABILITÀ CRITICA: Permette accesso ai file .htaccess (normalmente bloccati!)
# Rimuove la restrizione sui file .ht* che Apache blocca di default
# E aggiunge una direttiva esplicita per permettere l'accesso
RUN sed -i '/<Files ".ht\*">/,/<\/Files>/d' /etc/apache2/sites-available/000-default.conf || true && \
    echo '' >> /etc/apache2/sites-available/000-default.conf && \
    echo '# VULNERABILITÀ: Permette accesso ai file .htaccess (normalmente bloccati!)' >> /etc/apache2/sites-available/000-default.conf && \
    echo '<Files ".htaccess">' >> /etc/apache2/sites-available/000-default.conf && \
    echo '    Require all granted' >> /etc/apache2/sites-available/000-default.conf && \
    echo '</Files>' >> /etc/apache2/sites-available/000-default.conf

# VULNERABILITÀ: Server signature esposta
RUN echo "ServerTokens Full" >> /etc/apache2/conf-available/security.conf && \
    echo "ServerSignature On" >> /etc/apache2/conf-available/security.conf

# Copia script di entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Usa entrypoint personalizzato
ENTRYPOINT ["docker-entrypoint.sh"]

# Espone la porta 80
EXPOSE 80

