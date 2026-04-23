FROM php:8.2-apache

# Install MySQL extension for PHP
RUN docker-php-ext-install mysqli

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html