#!/usr/bin/env bash
set -e

export PORT="${PORT:-10000}"

echo "Listen ${PORT}" > /etc/apache2/ports.conf

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link || true
php artisan optimize:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

if [ "${DB_CONNECTION:-}" = "pgsql" ] && [ -z "${DB_URL:-}" ]; then
  echo "ERROR: DB_CONNECTION is pgsql, but DB_URL is empty."
  echo "Add your full PostgreSQL connection string to the Render environment variable named DB_URL."
  echo "Do not put the connection string in DB_DATABASE."
  exit 1
fi

case "${DB_DATABASE:-}" in
  postgres://*|postgresql://*)
    echo "ERROR: DB_DATABASE contains a PostgreSQL connection string."
    echo "Move that value to DB_URL and remove DB_DATABASE from Render environment variables."
    exit 1
    ;;
esac

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  echo "Running migrations on DB_CONNECTION=${DB_CONNECTION:-unset}"
  php artisan tinker --execute="dump(config('database.default'), config('database.connections.'.config('database.default').'.host'), config('database.connections.'.config('database.default').'.database'));" || true
  php artisan migrate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
