#!/bin/bash
set -e

echo "Starting UserPlay application in ${APP_ENV:-development} mode..."

# Wait for MySQL to be ready
echo "Waiting for MySQL database to be ready..."
until mysqladmin ping -h "${MYSQL_DB_HOST}" -P "${MYSQL_DB_PORT}" -u"${MYSQL_USER}" -p"${MYSQL_PASSWORD}" --ssl=0 ; do
    echo "Waiting for MySQL..."
    sleep 2
done

echo "MySQL is ready!"

# Install/update Composer dependencies based on environment
if [ "${APP_ENV}" = "production" ]; then
    echo "Installing production dependencies..."
    composer install --no-dev --optimize-autoloader --no-scripts --prefer-dist
    composer run-script post-install-cmd || true
else
    echo "Installing development dependencies..."
    composer install --prefer-dist
fi

# Set proper permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html
chmod +x /var/www/html/bin/cli

## Run database migrations
echo "Running database migrations..."
bin/cli phinx:dump-phinx-config
vendor/bin/phinx migrate -c temp/phinx.json -e all
#vendor/bin/phinx status -c temp/phinx.json -e all

alias ll='ls -l'

echo "Starting Apache..."
# Start Apache in foreground
apache2-foreground
