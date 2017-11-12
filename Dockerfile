FROM php:7.1-alpine

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin  --filename=composer

# Inject application code
COPY . /srv/

WORKDIR /srv
ENV CONTRACTS_DIR=examples
RUN composer install --no-dev && ./bin/phpacto validate

# Start
USER www-data
CMD php -S 0.0.0.0:8000 bin/server_mock.php

EXPOSE 8000
