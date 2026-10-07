#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force
touch database/database.sqlite
php artisan migrate:fresh --seed --force

exec php artisan serve --host=0.0.0.0 --port=8000
