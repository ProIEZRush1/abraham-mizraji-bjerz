# Client site — Laravel + Vue (Inertia). Assets are pre-built and committed, so the
# image build is just composer (fast); no npm at build time. Served by `php artisan serve`
# under an auto-respawn loop (see docker/start.sh) so a single request error can never
# leave the app permanently down. TLS is terminated by the upstream proxy; we listen on :8080.
FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev libsqlite3-dev libpq-dev unzip git \
    && docker-php-ext-install zip bcmath pdo pdo_sqlite pdo_pgsql pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN cp .env.production .env \
    && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p database storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R ug+rwX storage bootstrap/cache database \
    && chmod +x docker/start.sh

EXPOSE 8080

CMD ["sh", "docker/start.sh"]
