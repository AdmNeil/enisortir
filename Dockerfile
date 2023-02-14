FROM php:8.2-apache

RUN apt-get update && apt-get upgrade -y

RUN apt-get install -y nano make libicu-dev g++ libpng-dev libxml2-dev libzip-dev libonig-dev libxslt-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl pdo pdo_mysql zip xml

RUN cd ~

##install composer & symfony

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php && \
php /tmp/composer-setup.php --install-dir=/usr/local/bin --filename=composer && \
curl -sS https://get.symfony.com/cli/installer | bash && \
mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

COPY vhosts.conf /etc/apache2/sites-enabled
