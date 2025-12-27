#!/bin/sh

set -e

# Set PORT for nginx (Render uses PORT env variable, default to 10000)
export PORT=${PORT:-10000}

# Wait for database to be ready (if using external database)
# For SQLite, we just need to ensure the directory exists
mkdir -p /var/www/html/database
touch /var/www/html/database/database.sqlite

# Set permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chmod 664 /var/www/html/database/database.sqlite

# Run migrations (only if not already run)
php artisan migrate --force || true

# Run seeders (only if database is empty)
php artisan db:seed --class=QuestionSeeder --force || true

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Substitute PORT in nginx config template
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Start supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

