#!/bin/sh

set -e

# Wait for MySQL to be ready
echo "⏳ Waiting for database..."
until nc -z -v -w30 "$DB_HOST" "$DB_PORT"
do
  echo "⏳ Waiting for MySQL at $DB_HOST:$DB_PORT..."
  sleep 5
done

echo "✅ Database is up. Running setup..."

# Install PHP dependencies
composer install

# Install Node dependencies
npm install
npm run build

# Run migrations (optional)
php artisan migrate --seed --force

# Start Laravel server
exec php artisan serve --host=0.0.0.0 --port=8000
