# Image de démonstration pour héberger l'API gratuitement (Render, Fly.io, Koyeb…).
FROM php:8.2-cli

# Extensions PHP nécessaires à Doctrine + SQLite.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev unzip git \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

ENV APP_ENV=prod
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p var \
    && composer dump-env prod

# Au démarrage : applique les migrations puis sert l'application.
# $PORT est fourni par l'hébergeur (défaut 8000 en local).
CMD php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration \
    && php -S 0.0.0.0:${PORT:-8000} -t public public/index.php
