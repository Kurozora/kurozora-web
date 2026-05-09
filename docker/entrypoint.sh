#!/bin/sh
Red='\033[0;31m'          # Red
Green='\033[0;32m'        # Green
echo ""
echo "***********************************"
echo "     Starting Docker Container     "
echo "***********************************"

set -e

## Check if the artisan file exists
if [ -f /var/www/html/artisan ]; then
    # Optimize and update Laravel
    php /var/www/html/artisan auth:clear-resets
#    php /var/www/html/artisan migrate --force

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

## Render nginx config from template using runtime env vars
echo ""
echo "***********************************"
echo "    Rendering nginx config...      "
echo "***********************************"
envsubst '${DOMAIN_NAME}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo ""
echo "***********************************"
echo "      Starting Supervisord...      "
echo "***********************************"
supervisord -c /etc/supervisor/supervisord.conf &

echo ""
echo "***********************************"
echo "         Starting NGINX...         "
echo "***********************************"
nginx -g 'daemon off;'
