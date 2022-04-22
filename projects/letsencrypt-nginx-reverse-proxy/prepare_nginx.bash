#!/usr/bin/env bash
set -e

rm /etc/nginx/conf.d/*

for RULE in `echo "${RULES}" | sed "s/,/\n/g"`; do (
    DOMAIN=$(echo "${RULE}" | cut -d ">" -f 1)
    TARGET=$(echo "${RULE}" | cut -d ">" -f 2)
    SSL_PATH="/etc/letsencrypt/live/${DOMAIN}"
    SSL_CERT="${SSL_PATH}/fullchain.pem"
    SSL_KEY="${SSL_PATH}/privkey.pem"

    if [[ ! -e "${SSL_PATH}" ]]; then (
        mkdir --parent "${SSL_PATH}"
    ); fi

    if [[ ! -e "${SSL_CERT}" || ! -e "${SSL_KEY}" ]]; then (
        cp /selfsigned.crt "${SSL_CERT}"
        cp /selfsigned.key "${SSL_KEY}"
        touch "${SSL_PATH}/.fake"
    ); fi

    cat > "/etc/nginx/conf.d/${DOMAIN}.conf" << EoS
${UPSTREAMS}

server {
  listen 80;
  listen [::]:80;

  server_name ${DOMAIN};

  location '/' {
    return 301 https://\$server_name\$request_uri;
  }

  location '/.well-known' {
    root /tmp/letsencrypt;
  }
}

server {
  listen 443 ssl;
  listen [::]:443 ssl;

  server_name ${DOMAIN};

  ssl_certificate ${SSL_CERT};
  ssl_certificate_key ${SSL_KEY};

  location '/' {
    ${PROXY_OPTIONS}
    ${PROXY_HEADERS}
    ${PROXY_ADDITIONAL_OPTIONS}
    proxy_pass http://${TARGET};
  }
}
EoS
); done

cat > "/etc/nginx/conf.d/default.conf" << EoS
${UPSTREAMS}

server {
  listen 80 default_server;
  listen [::]:80 default_server;

  ${DEFAULT_SERVER}
}

server {
  listen 443 ssl default_server;
  listen [::]:443 ssl default_server;

  ssl_certificate /selfsigned.crt;
  ssl_certificate_key /selfsigned.key;

  ${DEFAULT_SERVER}
}
EoS

cat > "/etc/nginx/conf.d/localhost.conf" << EoS
server {
  listen 127.0.0.1:80;
  listen [::1]:80;

  server_name localhost;

  return 204;
}
EoS
