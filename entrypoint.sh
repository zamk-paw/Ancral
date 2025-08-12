#!/bin/sh
set -e
mkdir -p /var/www/data /var/www/cdn-public
chown -R www-data:www-data /var/www/data || true
chown -R www-data:www-data /var/www/cdn-public || true
exec "$@"
