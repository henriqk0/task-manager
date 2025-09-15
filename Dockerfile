FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libpng-dev \
    libzip-dev default-mysql-client

RUN docker-php-ext-install pdo pdo_mysql zip gd

RUN a2enmod rewrite

WORKDIR /var/www

COPY . /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer clear-cache

# RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-scripts --no-autoloader
# RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --prefer-dist --no-autoloader --no-plugins
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --prefer-dist --no-interaction --no-scripts

RUN php bin/console importmap:install

RUN php bin/console doctrine:migrations:migrate

EXPOSE 80

# config sessoes para o container (util para alguns endpoints)
COPY docker/php/custom-php.ini /usr/local/etc/php/conf.d/custom-php.ini

# config rotas do container
COPY docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf


RUN mkdir -p /var/lib/php/sessions && chown -R www-data:www-data /var/lib/php/sessions

RUN mkdir -p var && chown -R www-data:www-data var


CMD [ "apache2-foreground" ]
