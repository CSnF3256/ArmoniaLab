#!/bin/sh
set -e

if [ ! -f .env ]; then
    cp .env.example .env
fi

# Guardamos una clave válida dentro del contenedor antes de iniciar Laravel.
# Esto evita que una variable vacía o mal propagada por el proceso de Render
# reemplace la clave cuando `artisan serve` crea el servidor PHP.
if php -r '$key = getenv("APP_KEY") ?: ""; $raw = str_starts_with($key, "base64:") ? base64_decode(substr($key, 7), true) : $key; exit(is_string($raw) && strlen($raw) === 32 ? 0 : 1);'; then
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
    echo "APP_KEY de Render guardada para Laravel."
else
    php artisan key:generate --force --no-ansi
    echo "APP_KEY generada automáticamente para el contenedor."
fi
unset APP_KEY

touch database/database.sqlite
php artisan migrate --force
php artisan db:seed --force

PORT_TO_USE="${PORT:-8000}"
echo "Iniciando servidor ArmoníaLab en puerto ${PORT_TO_USE}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT_TO_USE}" --no-reload
