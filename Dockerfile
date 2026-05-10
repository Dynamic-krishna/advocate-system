FROM php:8.2-apache

# Install PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache rewrite module
RUN a2enmod rewrite

# Create index.php that redirects to register.php
RUN echo '<?php header("Location: register.php"); exit(); ?>' > /var/www/html/index.php

# Set DirectoryIndex to look for register.php first
RUN echo "DirectoryIndex register.php index.php index.html" > /etc/apache2/conf-available/docker-index.conf \
    && a2enconf docker-index

# Copy your project files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads directory
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

# Ensure Apache can serve files
RUN echo '<Directory /var/www/html/>\n\tOptions Indexes FollowSymLinks\n\tAllowOverride All\n\tRequire all granted\n</Directory>' >> /etc/apache2/apache2.conf

EXPOSE 80