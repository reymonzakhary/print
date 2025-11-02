FROM reymonzakhary/converter:base

# make the 'app' folder the current working directory
WORKDIR /var/www

COPY . .
COPY .env.example .env

RUN mkdir -p storage/logs && \
    chown -R nginx:nginx /var/www && \
    rm -rf html && \
    chmod -R 777 storage

RUN yum -y install pango.x86_64 libXcomposite.x86_64 libXcursor.x86_64 libXdamage.x86_64 libXext.x86_64 libXi.x86_64 libXtst.x86_64
RUN yum -y install cups-libs.x86_64 libXScrnSaver.x86_64 libXrandr.x86_64 GConf2.x86_64 alsa-lib.x86_64 atk.x86_64 gtk3.x86_64 ipa-gothic-fonts xorg-x11-fonts-100dpi xorg-x11-fonts-75dpi xorg-x11-utils xorg-x11-fonts-cyrillic xorg-x11-fonts-Type1 xorg-x11-fonts-misc libatk-bridge-2.0.so.0

RUN crontab -l | { cat; echo "* * * * * cd /var/www && php artisan tenancy:run schedule:run >> /dev/null 2>&1"; } | crontab - && \
    crontab -l | { cat; echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1"; } | crontab -

EXPOSE 9090

# try to run nginx
CMD ["/usr/bin/supervisord", "-n"]
