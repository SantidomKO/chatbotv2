FROM php:8.2-apache

# Prevent interactive prompts during build
ENV DEBIAN_FRONTEND=noninteractive

# Install build tools and libraries required by PHP extensions
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
     build-essential \
     pkg-config \
     libzip-dev \
     zlib1g-dev \
     libpng-dev \
     libjpeg62-turbo-dev \
     libfreetype6-dev \
     libxml2-dev \
     libonig-dev \
     zip \
     unzip \
     git \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo pdo_mysql mysqli mbstring zip xml \
 && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Ensure models directory exists and is writable
RUN mkdir -p /var/www/html/models && chown -R www-data:www-data /var/www/html/models

# Instalar composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'.PHP_EOL; } else { echo 'Installer corrupt'.PHP_EOL; unlink('composer-setup.php'); exit(1); }"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

EXPOSE 80

CMD ["apache2-foreground"]
