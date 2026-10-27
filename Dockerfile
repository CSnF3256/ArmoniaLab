FROM composer:2 AS dependencies

WORKDIR /app
COPY . .
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

FROM php:8.4-cli

WORKDIR /app
COPY --from=dependencies /app/vendor ./vendor
COPY . .

RUN cp .env.example .env && chmod +x docker/start.sh

EXPOSE 8000

CMD ["sh", "docker/start.sh"]
