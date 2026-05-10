FROM php:8.2-apache

# Install PostgreSQL driver
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Explicitly configure Apache to handle PHP files
RUN { \
        echo '<FilesMatch \.php$>'; \
        echo '    SetHandler application/x-httpd-php'; \
        echo '</FilesMatch>'; \
    } >> /etc/apache2/apache2.conf

# Set DirectoryIndex
RUN { \
        echo '<IfModule dir_module>'; \
        echo '    DirectoryIndex register.php index.php index.html'; \
        echo '</IfModule>'; \
    } > /etc/apache2/conf-available/docker-index.conf \
    && a2enconf docker-index

# Copy project files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && mkdir -p /var/www/html/uploads \
    && chmod 777 /var/www/html/uploads

# Verify PHP is working (debug)
RUN php -v

EXPOSE 80