# PHP 7.4 + Apache. O sistema ainda não foi validado em PHP 8.
FROM php:7.4-apache

# A base da imagem (Debian 11) saiu de linha: os pacotes agora estão em archive.debian.org.
RUN sed -i 's/deb.debian.org/archive.debian.org/' /etc/apt/sources.list \
 && apt-get update \
 && apt-get install -y --no-install-recommends libpng-dev libjpeg62-turbo-dev libfreetype6-dev libwebp-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
 && docker-php-ext-install -j"$(nproc)" mysqli gd \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/apache.conf /etc/apache2/conf-enabled/app.conf
COPY app/ /var/www/html/
COPY scripts/ /var/www/scripts/

RUN mkdir -p /var/www/html/_core/_uploads && chown -R www-data:www-data /var/www/html/_core/_uploads
