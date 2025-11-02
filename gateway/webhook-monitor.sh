#!/bin/bash
LOG_FILE="/var/log/webhook-monitor.log"

log_message() {
    echo "$(date): $1" | tee -a $LOG_FILE
}

# Check webhook queue health
UNHEALTHY=$(php /var/www/artisan webhook:status --json 2>/dev/null | jq -r 'to_entries[] | select(.value.status == "unhealthy") | .key' 2>/dev/null)

if [ ! -z "$UNHEALTHY" ]; then
    log_message "ALERT: Unhealthy webhook queues: $UNHEALTHY"
fi

# Check webhook workers
STOPPED_WORKERS=$(supervisorctl status 2>/dev/null | grep webhook | grep -v RUNNING | wc -l)

if [ $STOPPED_WORKERS -gt 0 ]; then
    log_message "ALERT: $STOPPED_WORKERS webhook workers stopped, restarting..."
    supervisorctl restart webhook-workers:* >> $LOG_FILE 2>&1
fi

log_message "Webhook monitoring check completed"
