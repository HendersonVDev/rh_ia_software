FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    git curl zip unzip libzip-dev libpng-dev libonig-dev \
    libxml2-dev libssl-dev poppler-utils libpng-dev \
    && docker-php-ext-install pdo pdo_mysql zip bcmath mbstring gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN pecl install redis && docker-php-ext-enable redis

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist

RUN chmod -R 777 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]