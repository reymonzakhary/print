FROM centos:centos7.9.2009

MAINTAINER "ReymonZakhary" <reymon.zakhary@gmail.com>

RUN rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm \
 && rpm -Uvh https://rpms.remirepo.net/enterprise/remi-release-7.rpm

# tools
RUN yum -y install epel-release yum-utils iproute at curl crontabs cronie git vim net-tools python-pip sudo gcc-c++ make zip unzip

# normal updates
RUN yum -y update

# Install Nginx
RUN yum -y install nginx

RUN yum -y install yum-utils \
    && yum-config-manager --disable 'remi-php*' \
    && yum-config-manager --enable remi-php82 \
    && yum repolist \
    && yum -y install php php-{cli,fpm,mysqlnd,zip,devel,gd,mbstring,curl,xml,pear,bcmath,json,opcache,redis,memcache,bcmath,gmp} \
    && yum -y install pdftk


#Install Nginx and PHP 82
RUN yum install -y php-sodium php-cli php-common postgresql-devel php-devel php-fpm php-gd php-intl php-mbstring \
    php-zip php-soap php-xml php-xmlrpc php-xmlrpc php-mysqlnd php-pdo php-pgsql php-gd php-gmp poppler-utils gcc \
    php-mcrypt php-mysql php-opcache php-pdo php-pear php-pecl-igbinary php-pecl-imagick php-process

#install imagemagic extension
ENV IMAGEMAGICK_VERSION="7.0.9-10"
ENV IMAGEMAGICK_LIB_RPM_URL="https://imagemagick.org/download/linux/CentOS/x86_64/ImageMagick-libs-${IMAGEMAGICK_VERSION}.x86_64.rpm"
ENV IMAGEMAGICK_RPM_URL="https://imagemagick.org/download/linux/CentOS/x86_64/ImageMagick-${IMAGEMAGICK_VERSION}.x86_64.rpm"

RUN export HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" $IMAGEMAGICK_RPM_URL)
RUN if [ $HTTP_CODE != "200" ] ; then echo "$IMAGEMAGICK_RPM_URL does not exist" && exit 1 ; fi

RUN curl -s -S "https://download-ib01.fedoraproject.org/pub/epel/7/x86_64/Packages/l/libraqm-0.7.0-1.el7.x86_64.rpm" -o libraqm.rpm && \
    curl -s -S $IMAGEMAGICK_LIB_RPM_URL -o imagemagick-lib.rpm && \
    curl -s -S $IMAGEMAGICK_RPM_URL -o imagemagick.rpm

RUN yum -y update && \
    yum localinstall -y libraqm.rpm imagemagick*.rpm && \
    rm -f imagemagick*.rpm && \
    yum clean all

RUN yum install ImageMagick -y

#Install mongo extension
RUN pecl install mongodb

# Install Composer.
RUN curl -sS https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

#Install Nodejs 20
RUN curl --silent --location https://rpm.nodesource.com/setup | bash - && yum install -y nodejs

RUN curl -sL https://dl.yarnpkg.com/rpm/yarn.repo | sudo tee /etc/yum.repos.d/yarn.repo && \
    yum install -y yarn

RUN yum -y install python36 && \
    yum -y install python36-devel && \
    yum -y install python36-setuptools

# Install supervisor ### this command not found pip install supervisor-stdout
RUN yum -y install supervisor
#### RUN yum -y install supervisor && pip install supervisor-stdout
# RUN pip install supervisor-stdout

#RUN yum install -y python-setuptools && easy_install supervisor
COPY ./config/supervisord.conf /etc/supervisord.conf

#RUN sed -i 's/^listen.allowed_clients/;listen.allowed_clients/' /etc/php-fpm.d/www.conf && \
RUN sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php-fpm.conf


## cron tab
RUN mkdir -p /var/www && \
    mkdir -p /var/run/php-fpm && \
    chown -R nginx:nginx /var/www && \
    chown -R nginx:nginx /var/run/php-fpm

#RUN Nginx and PHP-FPM
COPY ./config/v-host.conf /etc/nginx/conf.d/default.conf
COPY ./config/nginx.conf /etc/nginx/
COPY ./config/php.ini /etc/php.ini
COPY ./config/www.conf /etc/php-fpm.d/www.conf

#PHP session
RUN mkdir -p /var/lib/php/session/ && \
    chmod -R 777 /var/lib/php/

# make the 'app' folder the current working directory
WORKDIR /var/www

EXPOSE 80

# try to run nginx
CMD ["/usr/bin/supervisord", "-n"]
