FROM php:7.4-fpm-alpine

RUN apk add --no-cache nginx supervisor wget sqlite-dev sqlite

RUN mkdir -p /run/nginx

COPY docker/nginx.conf /etc/nginx/nginx.conf

RUN mkdir -p /app
COPY . /app


RUN sh -c "wget http://getcomposer.org/composer.phar && chmod a+x composer.phar && mv composer.phar /usr/local/bin/composer"
RUN cd /app && \
    /usr/local/bin/composer install --no-dev

RUN chown -R www-data: /app

RUN cd /app && php artisan migrate:fresh
RUN chown -R www-data:www-data /app/database/database.sqlite

CMD sh /app/docker/startup.sh
