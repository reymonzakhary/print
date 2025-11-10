#!/bin/bash
# Fix crontab in running container - replace old tenancy:run with new tenants:artisan

echo "Fixing crontab for stancl/tenancy..."

# Remove old tenancy:run cron job and add new tenants:artisan one
crontab -l 2>/dev/null | grep -v "tenancy:run" > /tmp/current_crontab

# Add the new tenants:artisan cron job if it doesn't exist
if ! grep -q "tenants:artisan" /tmp/current_crontab; then
    echo "* * * * * cd /var/www && php artisan tenants:artisan 'schedule:run' >> /dev/null 2>&1" >> /tmp/current_crontab
fi

# Install the new crontab
crontab /tmp/current_crontab

echo "Current crontab:"
crontab -l

echo "Done! Crontab has been updated."
