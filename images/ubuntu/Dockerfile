# Use Ubuntu 22.04 as the base image
FROM ubuntu:22.04

# Maintainer information
LABEL maintainer="Reymon Zakhary <reymon.zakhary@gmail.com>"

# Environment variables
ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_VERSION=8.3

# Update base image and install essential packages
# Group packages by purpose and reduce layers by combining installations
RUN apt-get update && apt-get install -y \
    # System utilities
    software-properties-common \
    gnupg \
    dirmngr \
    curl \
    wget \
    lsb-release \
    ca-certificates \
    apt-transport-https \
    vim \
    net-tools \
    iproute2 \
    sudo \
    git \
    unzip \
    zip \
    cron \
    build-essential \
    # Image processing
    poppler-utils \
    jpegoptim \
    optipng \
    pngquant \
    gifsicle \
    libavif-bin \
    ghostscript \
    imagemagick \
    # Services
    beanstalkd \
    supervisor \
    python3-pip \
    # Clean up apt cache to reduce image size
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Add PHP repository
RUN add-apt-repository ppa:ondrej/php -y

# Install Node.js 20.x and Yarn
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && curl -sL https://dl.yarnpkg.com/debian/pubkey.gpg | gpg --dearmor -o /usr/share/keyrings/yarn-archive-keyring.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/yarn-archive-keyring.gpg] https://dl.yarnpkg.com/debian stable main" > /etc/apt/sources.list.d/yarn.list

# Install PHP, Node.js, Yarn, Nginx, Redis in a single layer to reduce image size
RUN apt-get update && apt-get install -y \
    # PHP and extensions
    php${PHP_VERSION} \
    php${PHP_VERSION}-cli \
    php${PHP_VERSION}-fpm \
    php${PHP_VERSION}-mysql \
    php${PHP_VERSION}-zip \
    php${PHP_VERSION}-dev \
    php${PHP_VERSION}-gd \
    php${PHP_VERSION}-mbstring \
    php${PHP_VERSION}-curl \
    php${PHP_VERSION}-xml \
    php${PHP_VERSION}-bcmath \
    php${PHP_VERSION}-opcache \
    php-pear \
    php${PHP_VERSION}-redis \
    php${PHP_VERSION}-memcache \
    php${PHP_VERSION}-gmp \
    php${PHP_VERSION}-intl \
    php${PHP_VERSION}-soap \
    php${PHP_VERSION}-pgsql \
    php${PHP_VERSION}-imagick \
    # Node.js and Yarn
    nodejs \
    yarn \
    # Nginx
    nginx \
    nginx-extras \
    # Redis
    redis-server \
    # Clean up apt cache
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    # Install SVGO using npm from Node.js
    && npm install -g svgo

# Install MongoDB extension
RUN pecl install mongodb \
    && echo "extension=mongodb.so" > /etc/php/${PHP_VERSION}/mods-available/mongodb.ini \
    && ln -s /etc/php/${PHP_VERSION}/mods-available/mongodb.ini /etc/php/${PHP_VERSION}/cli/conf.d/20-mongodb.ini \
    && ln -s /etc/php/${PHP_VERSION}/mods-available/mongodb.ini /etc/php/${PHP_VERSION}/fpm/conf.d/20-mongodb.ini

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Install supervisor-stdout
RUN pip3 install supervisor-stdout

# Copy configuration files
COPY ./config/php.ini /etc/php/${PHP_VERSION}/fpm/php.ini
COPY ./config/www.conf /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf
COPY ./config/nginx.conf /etc/nginx/nginx.conf
COPY ./config/v-host.conf /etc/nginx/conf.d/default.conf
COPY ./config/supervisord.conf /etc/supervisor/supervisord.conf

# Clean up default Nginx sites
RUN rm -f /etc/nginx/sites-enabled/*

# Create necessary directories and set permissions
RUN mkdir -p /var/log/nginx /var/log/php-fpm /tmp/cron /tmp/beanstalkd /tmp/supervisor /tmp/php /run/php/ /var/lib/php/session/ \
    && touch /tmp/php${PHP_VERSION}-fpm.log \
    && chown -R www-data:www-data /var/log/nginx /var/log/php-fpm /tmp/cron /tmp/beanstalkd /tmp/supervisor /tmp/php${PHP_VERSION}-fpm.log /tmp/php /var/www /var/lib/php/session/ \
    && chmod 755 /tmp \
    && chmod -R 777 /run/php/

# Expose ports
EXPOSE 80 9001

# Set the working directory
WORKDIR /var/www

# Start Supervisor
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]