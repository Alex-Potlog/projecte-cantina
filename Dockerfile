# Utilitzem la imatge oficial de PHP amb Apache
FROM php:8.2-apache

# Instal·lem extensions de PHP si són necessàries
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Habilitem el mod_rewrite i mod_headers d'Apache
RUN a2enmod rewrite headers

# Copiem els fitxers del projecte al directori d'Apache
COPY ./public /var/www/html/
COPY ./php /var/www/html/php/
COPY ./data /var/www/html/data/
COPY ./tiquets /var/www/html/tiquets/
COPY ./users /var/www/html/users/

# Creem el directori de logs
RUN mkdir -p /var/www/html/logs

# Configurem els permisos adequats
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 777 /var/www/html \
    && chmod -R 777 /var/www/html/logs

# Exposem el port 80
EXPOSE 80

# Comando per iniciar Apache en primer pla
CMD ["apache2-foreground"]
