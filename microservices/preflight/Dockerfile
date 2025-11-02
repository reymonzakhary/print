FROM reymonzakhary/gateway:8.1.9

# make the 'app' folder the current working directory
WORKDIR /var/www

COPY . .
COPY .env.example .env

RUN mkdir -p storage/logs && \
    chown -R nginx:nginx /var/www && \
    rm -rf html

RUN crontab -l | { cat; echo "* * * * * cd /var/www && php artisan tenancy:run schedule:run >> /dev/null 2>&1"; } | crontab - && \
    crontab -l | { cat; echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1"; } | crontab -

EXPOSE 80

# try to run nginx
CMD ["/usr/bin/supervisord", "-n"]
