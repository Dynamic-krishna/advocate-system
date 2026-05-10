FROM php:8.2-apache

# Install PostgreSQL support
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable PHP (most important part!)
RUN a2enmod php8.2 || true

# Copy your application
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Ensure Apache serves PHP files correctly
RUN echo "DirectoryIndex register.php index.php index.html" > /etc/apache2/conf-available/docker-php.conf \
    && a2enconf docker-php

# Create uploads directory
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

EXPOSE 80

# Restart Apache to apply changes
RUN service apache2 restart || true