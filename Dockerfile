FROM php:8.2-apache

# Update dan install Ghostscript
RUN apt-get update && apt-get install -y ghostscript

# Install ekstensi MySQL untuk PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite Apache
RUN a2enmod rewrite

# Pastikan folder uploads bisa ditulis oleh www-data
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 100M" >> /usr/local/etc/php/conf.d/uploads.ini