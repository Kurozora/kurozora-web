#!/bin/sh
Red='\033[0;31m'

ROLE="${CONTAINER_ROLE:-all}"
ANUBIS_ENABLED="${ANUBIS_ENABLED:-true}"

echo ""
echo "***********************************"
echo "  Starting Docker Container        "
echo "  Role: ${ROLE}                    "
echo "***********************************"

set -e

# Launches Anubis as a background process for web-facing roles.
start_anubis() {
    if [ "$ANUBIS_ENABLED" != "true" ]; then
        echo "Anubis disabled (ANUBIS_ENABLED!=true)"
        return
    fi
    if [ -z "${ED25519_PRIVATE_KEY_HEX}" ]; then
        echo "${Red}Anubis requires the ED25519_PRIVATE_KEY_HEX env var"
        exit 1
    fi

    echo ""
    echo "***********************************"
    echo "        Starting Anubis...         "
    echo "***********************************"

    BIND="${BIND:-:8924}" \
    TARGET="${TARGET:-http://127.0.0.1:80}" \
    COOKIE_DOMAIN="${COOKIE_DOMAIN:-${DOMAIN_NAME}}" \
    POLICY_FNAME="${POLICY_FNAME:-/etc/anubis/policy.yaml}" \
    DIFFICULTY="${DIFFICULTY:-4}" \
    OG_PASSTHROUGH="${OG_PASSTHROUGH:-true}" \
    USE_REMOTE_ADDRESS="${USE_REMOTE_ADDRESS:-true}" \
        /usr/local/bin/anubis &
}

if [ -f /var/www/html/artisan ]; then
    php /var/www/html/artisan config:cache
    php /var/www/html/artisan route:cache

    ## Web-only side-effects
    if [ "$ROLE" = "web" ] || [ "$ROLE" = "all" ]; then
        php /var/www/html/artisan auth:clear-resets
        php /var/www/html/artisan scout:sync-index-settings
    fi
else
    echo "${Red}artisan file not found"
    exit 1
fi

case "$ROLE" in
    web)
        start_anubis

        echo ""
        echo "***********************************"
        echo "        Starting FrankenPHP...     "
        echo "***********************************"
        exec php /var/www/html/artisan octane:frankenphp \
            --host=0.0.0.0 \
            --port=80 \
            --workers="${OCTANE_WORKERS:-auto}" \
            --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
            --caddyfile=/etc/caddy/Caddyfile \
            --log-level="${CADDY_LOG_LEVEL:-INFO}"
        ;;

    worker)
        echo ""
        echo "***********************************"
        echo "     Starting queue worker...      "
        echo "***********************************"
        exec php /var/www/html/artisan queue:work \
            --sleep=3 \
            --tries=3 \
            --backoff=5 \
            --max-time=3600
        ;;

    scrape-worker)
        echo ""
        echo "***********************************"
        echo "    Starting scrape worker...      "
        echo "***********************************"
        exec php /var/www/html/artisan queue:work redis \
            --queue=scrape \
            --sleep=3 \
            --tries=3 \
            --backoff=60 \
            --max-time=3600
        ;;

    scheduler)
        echo ""
        echo "***********************************"
        echo "        Starting scheduler...      "
        echo "***********************************"
        exec php /var/www/html/artisan schedule:work
        ;;

    reverb)
        echo ""
        echo "***********************************"
        echo "        Starting Reverb...         "
        echo "***********************************"
        exec php /var/www/html/artisan reverb:start --host=0.0.0.0 --port=8080
        ;;

    all)
        touch /var/log/supervisord.log

        echo ""
        echo "***********************************"
        echo "      Starting Supervisord...      "
        echo "      (queue worker + scheduler)   "
        echo "***********************************"
        supervisord -c /etc/supervisor/supervisord.conf

        start_anubis

        echo ""
        echo "***********************************"
        echo "        Starting FrankenPHP...     "
        echo "***********************************"
        exec php /var/www/html/artisan octane:frankenphp \
            --host=0.0.0.0 \
            --port=80 \
            --workers="${OCTANE_WORKERS:-auto}" \
            --max-requests="${OCTANE_MAX_REQUESTS:-500}" \
            --caddyfile=/etc/caddy/Caddyfile \
            --log-level="${CADDY_LOG_LEVEL:-INFO}"
        ;;

    *)
        echo "${Red}Unknown CONTAINER_ROLE: ${ROLE}"
        echo "${Red}Valid roles: web, worker, scrape-worker, scheduler, reverb, all"
        exit 1
        ;;
esac
