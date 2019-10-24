#!/usr/bin/env sh
set -e
set -x

/init.sh
nginx -g "daemon off;" &
NGINX_PID=$!
/letsencrypt.sh
kill -2 "${NGINX_PID}"
sleep 2

while (true); do (
    nginx -g "daemon off;" &
    NGINX_PID=$!
    for I in $(seq 1 120960); do (
        sleep 5
        curl -f http://127.0.0.1/ > /dev/null 2>&1
    ); done
    /letsencrypt.sh
    kill -2 "${NGINX_PID}"
    sleep 2
); done;
