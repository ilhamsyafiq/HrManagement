FROM php:8.2-cli

# Install system dependencies (libicu-dev + intl are required by Filament formatting)
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libicu-dev nodejs npm \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring xml bcmath gd zip opcache intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# Install Node dependencies and build assets
COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build \
    && composer dump-autoload --optimize

# Set permissions
RUN chmod -R 775 storage bootstrap/cache \
    && mkdir -p storage/logs storage/framework/sessions storage/framework/views storage/framework/cache/data

EXPOSE 8080

# On each boot: run migrations, seed the Super Admin (idempotent), link the public
# disk, cache config/routes/views, then serve. Env vars are injected by Railway at
# runtime, so caching happens here (not at build time).
CMD php artisan migrate --force \
    && php artisan db:seed --class=ProductionSeeder --force \
    && (php artisan storage:link || true) \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache \
    && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
