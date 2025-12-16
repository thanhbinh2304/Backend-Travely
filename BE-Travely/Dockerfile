FROM php:8.2-fpm-alpine

WORKDIR /var/www

# System deps
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    git \
    icu-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    zip \
    unzip \
    $PHPIZE_DEPS

# PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd

# Redis extension (BẮT BUỘC)
RUN pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# App source
COPY . .

# Install PHP deps
RUN composer install --no-dev --optimize-autoloader

RUN php artisan key:generate || true
RUN php artisan config:clear
RUN php artisan route:clear
RUN php artisan cache:clear

# Permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Nginx + supervisor
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

EXPOSE 80
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
# End of Dockerfile