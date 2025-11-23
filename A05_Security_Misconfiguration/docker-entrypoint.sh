#!/bin/bash
set -e

# Crea directory se non esistono
mkdir -p /var/www/html/uploads
mkdir -p /var/www/html/backups
mkdir -p /var/www/html/logs
mkdir -p /var/www/html/admin

# Imposta permessi corretti
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod 755 /var/www/html/uploads /var/www/html/backups /var/www/html/logs /var/www/html/admin

# Avvia Apache
exec apache2-foreground

