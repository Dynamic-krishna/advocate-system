FROM php:8.2-apache

# Install PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy all files to Apache web root
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Create uploads directory
RUN mkdir -p /var/www/html/uploads && chmod 777 /var/www/html/uploads

EXPOSE 80