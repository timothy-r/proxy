FROM ubuntu:latest

MAINTAINER Tim Rodger <tim.rodger@gmail.com>

EXPOSE 80

RUN apt-get update -qq && \
    apt-get install -y \
    nginx \
    php5-cli \
    php5-fpm \
    curl \
    supervisor \
    git

# configure server applications

RUN echo "daemon off;" >> /etc/nginx/nginx.conf
ADD ./build/nginx/default /etc/nginx/sites-enabled/default
ADD ./build/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
ADD ./build/php-fpm/php-fpm.conf /etc/php5/fpm/php-fpm.conf

RUN echo "cgi.fix_pathinfo = 0;" >> /etc/php5/fpm/php.ini

RUN curl -sS https://getcomposer.org/installer | php \
  && mv composer.phar /usr/bin/composer

CMD ["/home/app/run.sh"]

# Move application files into place
COPY src/ /home/app/

RUN chmod +x /home/app/run.sh

# remove any development cruft
RUN rm -rf /home/app/vendor/*

WORKDIR /home/app

# Install dependencies
RUN composer install --prefer-dist && \
    apt-get clean

USER root

# forward request and error logs to docker log collector
RUN ln -sf /dev/stdout /var/log/nginx/access.log
RUN ln -sf /dev/stderr /var/log/nginx/error.log