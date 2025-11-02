FROM centos:8

MAINTAINER "ReymonZakhary" <reymon.zakhary@gmail.com>

# Update base image and set repositories to use CentOS vault
RUN sed -i 's/mirrorlist/#mirrorlist/g' /etc/yum.repos.d/CentOS-* && \
    sed -i 's|#baseurl=http://mirror.centos.org|baseurl=http://vault.centos.org|g' /etc/yum.repos.d/CentOS-* && \
    yum install -y glibc-langpack-en

# Install EPEL and Remi repository
RUN dnf install -y epel-release && \
    dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.4.rpm

# Update dnf cache and repositories
RUN dnf clean all && \
    dnf -y update --nobest

# Install essential tools
RUN dnf -y install yum-utils iproute at curl crontabs cronie git vim net-tools python3-pip sudo gcc-c++ make zip unzip

# Install Nginx
RUN dnf -y install nginx

# Enable Remi PHP 8.2 repository and install PHP 8.2 and related packages
RUN dnf -y install yum-utils && \
    dnf module reset php -y && \
    dnf module enable php:remi-8.2 -y && \
    dnf repolist && \
    dnf -y install php php-cli php-fpm php-mysqlnd php-zip php-devel php-gd php-mbstring php-curl php-xml php-pear php-bcmath php-json php-opcache php-redis php-memcache php-gmp && \
    dnf -y install pdftk

# Install additional PHP packages and dependencies
RUN dnf install -y php-sodium php-common postgresql-devel php-devel php-fpm php-gd php-intl php-mbstring php-zip php-soap php-xml php-xmlrpc php-mysqlnd php-pdo php-pgsql php-gd php-gmp poppler-utils gcc php-mcrypt php-mysql php-opcache php-pdo php-pear php-pecl-igbinary php-pecl-imagick php-process

# Install ImageMagick
RUN dnf install -y ImageMagick

# Install MongoDB extension
RUN dnf -y install php-pear php-devel && \
    pecl install mongodb && \
    echo "extension=mongodb.so" > /etc/php.d/20-mongodb.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Install Node.js 20
RUN curl -fsSL https://rpm.nodesource.com/setup_20.x | bash - && \
    dnf install -y nodejs

# Install Yarn
RUN curl -sL https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo && \
    dnf install -y yarn

# Install Python 3.6 and related packages
RUN dnf install -y python3 python3-devel && \
    python3 -m pip install --upgrade pip setuptools

# Install Supervisor and supervisor-stdout
RUN dnf install -y supervisor && \
    python3 -m pip install supervisor-stdout

# Copy Supervisor configuration
COPY ./config/supervisord.conf /etc/supervisord.conf

# Configure PHP-FPM
RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php-fpm.conf

# Set up directories and permissions
RUN mkdir -p /var/www /var/run/php-fpm && \
    chown -R nginx:nginx /var/www /var/run/php-fpm

# Copy configuration files
COPY ./config/v-host.conf /etc/nginx/conf.d/default.conf
COPY ./config/nginx.conf /etc/nginx/nginx.conf
COPY ./config/php.ini /etc/php.ini
COPY ./config/www.conf /etc/php-fpm.d/www.conf

# Set up PHP session directory
RUN mkdir -p /var/lib/php/session/ && \
    chmod -R 777 /var/lib/php/session

# Set the working directory
WORKDIR /var/www

# Expose port 80
EXPOSE 80

# Start services using Supervisor
CMD ["/usr/bin/supervisord", "-n"]