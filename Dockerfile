FROM yiisoftware/yii2-php:7.4-apache

RUN a2enmod rewrite

WORKDIR /app

ADD composer.lock composer.json /app/
RUN composer install --prefer-dist --optimize-autoloader && \
    composer clear-cache

COPY ./ /app/

RUN mkdir -p runtime web/assets && \
    chmod -R 775 runtime web/assets && \
    chown -R www-data:www-data runtime web/assets
