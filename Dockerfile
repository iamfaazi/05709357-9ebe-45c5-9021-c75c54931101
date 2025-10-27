FROM php:8.3-cli

# Set working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer files first (for better caching)
COPY composer.json composer.lock* ./

# Install PHP dependencies
RUN composer install --no-interaction --no-scripts --prefer-dist

# Copy application files
COPY . .

# Optimize autoloader
RUN composer dump-autoload --optimize

# Set proper permissions
RUN chmod -R 755 /app

RUN chmod +x bin/console

# Default command
CMD ["php", "bin/console"]