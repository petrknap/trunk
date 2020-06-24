#!/usr/bin/env bash
set -e

if [[ ! -e /tmp/letsencrypt ]]; then (
    mkdir /tmp/letsencrypt
); fi

COMMAND="/usr/local/bin/certbot-auto"
SWITCHES="--non-interactive --webroot --webroot-path /tmp/letsencrypt --agree-tos --register-unsafely-without-email"

eval "${COMMAND} renew ${SWITCHES} || ${IGNORE_LETS_ENCRYPT_RENEW_ERRORS} || ${IGNORE_LETS_ENCRYPT_ALL_ERRORS}"

for RULE in `echo "${RULES}" | sed "s/,/\n/g"`; do (
    DOMAIN=$(echo "${RULE}" | cut -d ">" -f 1);
    SSL_PATH="/etc/letsencrypt/live/${DOMAIN}"

    if [[ -e "${SSL_PATH}/.fake" ]]; then (
        tar -cf "${SSL_PATH}.tar" "${SSL_PATH}"
        rm -rf "${SSL_PATH}"
        eval "${COMMAND} certonly ${SWITCHES} -d ${DOMAIN} || ${IGNORE_LETS_ENCRYPT_OBTAIN_ERRORS} || ${IGNORE_LETS_ENCRYPT_ALL_ERRORS}"
        if [[ ! -e "${SSL_PATH}/fullchain.pem" ]]; then (
            tar -xf "${SSL_PATH}.tar"
        );
    ); fi
); done
