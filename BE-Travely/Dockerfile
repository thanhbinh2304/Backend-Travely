# Multi-stage build for Laravel Backend
# Stage 1: Build stage
FROM php:8.2-fpm-alpine AS builder

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libzip-dev \
    zip \
    unzip \
    postgresql-dev \
    libxml2-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies (without dev dependencies for production readiness)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Copy application files
COPY . .

# Generate optimized autoload files
RUN composer dump-autoload --optimize

# Stage 2: Production stage
FROM php:8.2-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install runtime dependencies only
RUN apk add --no-cache \
    curl \
    libpng \
    libjpeg-turbo \
    freetype \
    oniguruma \
    libzip \
    postgresql-libs \
    libxml2 \
    icu-libs \
    nginx \
    supervisor

# Install PHP extensions (same as builder)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy application from builder stage
COPY --from=builder --chown=www-data:www-data /var/www/html /var/www/html

# Create necessary directories and set permissions
RUN mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Expose port 80
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/api/health || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
