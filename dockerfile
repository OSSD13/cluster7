FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install essential dependencies and PHP extensions only
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    nodejs \
    npm \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
    && a2enmod rewrite \
    && sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application and install dependencies
COPY . .
RUN composer install --no-interaction --no-dev --optimize-autoloader \
    && npm install \
    && npm run build

# MY MVP!
#RUN chmod -R guo+w storage
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]