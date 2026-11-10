FROM php:8.2-apache

ARG LOCAL_GID=1000

RUN apt-get update \
    && apt-get install -y --no-install-recommends git libcurl4-openssl-dev libonig-dev \
    && git config --global --add safe.directory '*' \
    && docker-php-ext-install curl mbstring pdo_mysql \
    && sed -ri "s/^export APACHE_RUN_GROUP=.*/export APACHE_RUN_GROUP=#$LOCAL_GID/" /etc/apache2/envvars \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY index.php app_config.php /var/www/html/
COPY assets/ /var/www/html/assets/
COPY controller/ /var/www/html/controller/
COPY data/ /var/www/html/data/
COPY domain/ /var/www/html/domain/
COPY model/ /var/www/html/model/
COPY view/ /var/www/html/view/
