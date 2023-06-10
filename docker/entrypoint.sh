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
    # Optimize Laravel
    # Clear expired password reset tokens
    #php artisan auth:clear-resets && \
    #php artisan migrate --force
    php /var/www/html/artisan down
    php /var/www/html/artisan config:cache
    php /var/www/html/artisan view:cache
    php /var/www/html/artisan route:cache
    php /var/www/html/artisan event:cache
    php /var/www/html/artisan scout:sync-index-settings
    php /var/www/html/artisan storage:link --force
    php /var/www/html/artisan up

    echo "${Green} artisan file found, creating supervisor config..."

    ## Check if supervisor.d directory exists
    SUPERVISOR_CONF_DIR=/etc/supervisor/supervisor.d
    if [ -d /etc/supervisor/supervisor.d ]; then
      echo "${Green} supervisor.d directory found."
    else
      echo "${Green} Creating supervisor.d directory..."
      mkdir $SUPERVISOR_CONF_DIR
    fi

    ## Create Laravel scheduler and worker processes.
    ## Scheduler is used for running scheduled commands.
    ## Worker is used for completing queued jobs.
    WORKER_CONF=$SUPERVISOR_CONF_DIR/laravel-worker.ini
    touch $WORKER_CONF
    cat > "$WORKER_CONF" <<EOF
    [program:Laravel-scheduler]
    process_name=%(program_name)s_%(process_num)02d
    command=/bin/sh -c "while [ true ]; do (php /var/www/html/artisan schedule:run --verbose --no-interaction &); sleep 60; done"
    autostart=true
    autorestart=true
    numprocs=1
    user=www-data
    stdout_logfile=/var/log/laravel_scheduler.out.log
    redirect_stderr=true

    [program:Laravel-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/html/artisan queue:work
    autostart=true
    autorestart=true
    user=www-data
    redirect_stderr=true
    stdout_logfile=/var/log/laravel_worker.out.log
EOF
    echo  "${Green} Laravel supervisor config created"
else
    echo  "${Red} artisan file not found"
fi

touch /var/log/supervisord.log
echo "supervisord.log file created"

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
