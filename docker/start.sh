#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Render puede entregar APP_KEY como una variable vacía. En ese caso generamos
# una clave válida en .env y quitamos la variable vacía para que Laravel pueda leerla.
if [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force --no-ansi
    unset APP_KEY
    echo "APP_KEY generada automáticamente para el contenedor."
fi

touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

PORT_TO_USE="${PORT:-8000}"
echo "Iniciando servidor ArmoníaLab en puerto ${PORT_TO_USE}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT_TO_USE}"
