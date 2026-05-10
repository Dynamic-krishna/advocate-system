# Use the official PHP 8.2 image with Apache
FROM php:8.2-apache

# Install the PostgreSQL driver for PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache's rewrite module for clean URLs
RUN a2enmod rewrite

# Set the default entry point to your registration page
RUN echo "DirectoryIndex register.php index.php index.html" > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Copy your entire project into the default web directory
COPY . /var/www/html/

# Fix permissions: set the correct owner and file permissions.
# The 'www-data' user is what Apache uses to serve files.
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

# Explicitly grant Apache access to all files in the web directory
RUN echo '<Directory /var/www/html/>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

# Create an uploads folder and give Apache permission to write to it
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Expose the default Apache port
EXPOSE 80