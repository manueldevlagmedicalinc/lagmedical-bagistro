FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip curl zip nano \
    libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libwebp-dev libavif-dev \
    libonig-dev libxml2-dev libicu-dev libxslt1-dev \
    libssl-dev libcurl4-openssl-dev \
    default-mysql-client nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-avif \
    && docker-php-ext-install \
        pdo pdo_mysql mbstring zip exif pcntl bcmath calendar gd intl xsl soap opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN ln -s /usr/local/bin/php /usr/local/bin/php83

WORKDIR /var/www/html

CMD ["php-fpm"]
