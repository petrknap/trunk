#!/usr/bin/env bash
set -e

bash /prepare_nginx.bash
nginx -g "daemon off;" &

while (true); do (
    bash /call_letsencrypt.bash
    nginx -s reload
    sleep 1d
); done
