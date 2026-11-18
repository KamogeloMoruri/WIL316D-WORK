FROM php:8.3-cli

# pdo_sqlite is required for the SQLite database and curl is required for
# the optional Claude / Blockfrost API integrations.
RUN docker-php-ext-install pdo_sqlite \
    && apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000"]
