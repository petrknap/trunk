#!/usr/bin/env bash
set -e

bash /prepare_nginx.bash
nginx -g "daemon off;" &
bash /call_letsencrypt.bash
nginx -s reload

while (true); do (
    for I in $(seq 1 100800); do (
        sleep 5
        curl --max-time 2 -f http://127.0.0.1/ > /dev/null 2>&1
    ); done
    bash /call_letsencrypt.bash
    nginx -s reload
); done
