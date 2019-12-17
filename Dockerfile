FROM php:7.4-zts-alpine

RUN apk add --update bash

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin  --filename=composer

# Inject application code
COPY . /srv/

WORKDIR /srv
ENV CONTRACTS_DIR=examples
RUN composer install --optimize-autoloader && ./bin/phpacto validate

# Start
USER www-data
ENTRYPOINT ["./entrypoint.sh"]
CMD show-usage

EXPOSE 8000
