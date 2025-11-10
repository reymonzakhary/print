# Use the base image
FROM reymonzakhary/ubuntu:p8.3.8

# Switch to root to perform privileged operations
USER root

# Set the working directory
WORKDIR /var/www

# Ensure www-data group exists
RUN groupadd -r www-data || true

# Ensure www-data user exists and belongs to www-data group
RUN useradd -r -g www-data www-data || true

# Copy application files
COPY . .

# Set up permissions for the application directory
RUN mkdir -p storage/logs && \
    chown -R www-data:www-data /var/www && \
    rm -rf html

# Set up permissions for /tmp
RUN chmod -R 777 /tmp
# Create webhook monitoring script
COPY  webhook-monitor.sh /usr/local/bin/webhook-monitor.sh

# Make monitoring script executable
RUN chmod +x /usr/local/bin/webhook-monitor.sh

# Create log directories
RUN mkdir -p /var/log && \
    touch /var/log/webhook-monitor.log /var/log/webhook-rebalance.log && \
    chown www-data:www-data /var/log/webhook-*.log

# Add cron jobs (updated with webhook monitoring)
# For stancl/tenancy, use tenants:artisan to run commands for all tenants
RUN (crontab -l 2>/dev/null; echo "* * * * * cd /var/www && php artisan tenants:artisan 'schedule:run' >> /dev/null 2>&1") | crontab - && \
    (crontab -l 2>/dev/null; echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1") | crontab - && \
    (crontab -l 2>/dev/null; echo "*/5 * * * * /usr/local/bin/webhook-monitor.sh") | crontab - && \
    (crontab -l 2>/dev/null; echo "0 */6 * * * cd /var/www && php artisan webhook:rebalance >> /var/log/webhook-rebalance.log 2>&1") | crontab -
# Switch back to www-data user
#USER www-data

# Expose port
EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
