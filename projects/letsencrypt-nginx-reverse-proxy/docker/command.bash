#!/usr/bin/env bash
set -e

bash /prepare_nginx.bash
nginx -g "daemon off;" &
NGINX_PID=$!
bash /call_letsencrypt.bash
kill -2 "${NGINX_PID}"
sleep 2

while (true); do (
    nginx -g "daemon off;" &
    NGINX_PID=$!
    for I in $(seq 1 100800); do (
        sleep 5
        curl --max-time 2 -f http://127.0.0.1/ > /dev/null 2>&1
    ); done
    bash /call_letsencrypt.bash
    kill -2 "${NGINX_PID}"
    sleep 2
); done
