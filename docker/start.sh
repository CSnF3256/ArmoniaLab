#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

php artisan key:generate --force
touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

PORT_TO_USE="${PORT:-8000}"
echo "Iniciando servidor ArmoníaLab en puerto ${PORT_TO_USE}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT_TO_USE}"
