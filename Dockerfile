# Use official PHP with Apache
FROM php:8.2-apache

# Install PostgreSQL PDO
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Enable mod_rewrite for pretty URLs
RUN a2enmod rewrite

# Copy files
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Expose port 80
EXPOSE 80
