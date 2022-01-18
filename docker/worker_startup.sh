#!/bin/sh

#sed -i "s,LISTEN_PORT,$PORT,g" /etc/nginx/nginx.conf

php /app/artisan queue:work
