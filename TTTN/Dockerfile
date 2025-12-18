FROM php:8.2-apache

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# COPY đúng thư mục chứa code PHP
COPY project/ /var/www/html/

# Cấp quyền truy cập cho Apache
RUN chmod -R 755 /var/www/html

EXPOSE 80