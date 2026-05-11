#!/bin/sh
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
echo ""
echo "***********************************"
echo "  Starting Docker Container        "
echo "  (FrankenPHP + Octane runtime)    "
echo "***********************************"

set -e

## Check if the artisan file exists
if [ -f /var/www/html/artisan ]; then
    # Optimize and update Laravel
    php /var/www/html/artisan auth:clear-resets

    php /var/www/html/artisan config:cache
    php /var/www/html/artisan view:cache
    php /var/www/html/artisan route:cache
    php /var/www/html/artisan event:cache
    php /var/www/html/artisan scout:sync-index-settings
    php /var/www/html/artisan storage:link --force
else
    echo  "${Red} artisan file not found"
fi

touch /var/log/supervisord.log
echo "supervisord.log file created"

echo ""
echo "***********************************"
echo "      Starting Supervisord...      "
echo "      (queue worker + scheduler)   "
echo "***********************************"
supervisord -c /etc/supervisor/supervisord.conf

echo ""
echo "***********************************"
echo "        Starting FrankenPHP...     "
echo "***********************************"
exec frankenphp run --config /etc/caddy/Caddyfile
