#!/bin/sh
set -e

cd /var/www/html

exec php artisan serve --host=0.0.0.0 --port=8000
