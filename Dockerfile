# Multi-stage build for Laravel application

# Stage 1: Build stage
FROM php:8.2-fpm-alpine AS builder

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite \
    sqlite-dev \
    nodejs \
    npm \
    pkgconfig

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev dependencies for production)
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --optimize-autoloader

# Copy package files
COPY package.json package-lock.json ./

# Install Node dependencies
RUN npm ci

# Copy application files
COPY . .

# Complete Composer setup
RUN composer dump-autoload --optimize --classmap-authoritative

# Build assets
RUN npm run build

# Stage 2: Production stage
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    sqlite-dev \
    curl \
    gettext

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite zip

# Create necessary directories
RUN mkdir -p /var/www/html /var/log/supervisor /run/nginx

# Set working directory
WORKDIR /var/www/html

# Copy built application from builder stage
COPY --from=builder /var/www/html /var/www/html

# Copy nginx configuration template
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy startup script
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Expose port (Render uses PORT env variable, default to 10000)
# The actual port will be set via PORT environment variable
EXPOSE 10000

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
    CMD php artisan route:list || exit 1

# Start supervisor
CMD ["/usr/local/bin/start.sh"]

