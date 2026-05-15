# Use the official FrankenPHP image as a base
FROM dunglas/frankenphp:latest

# Install PostgreSQL PDO extension
# libpq-dev provides the necessary development files for PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install pdo_pgsql
