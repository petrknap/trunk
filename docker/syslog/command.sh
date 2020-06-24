#!/usr/bin/env sh
set -e

if [ "${PAPERTRAIL_SOCKET}" == "" ]; then (
    rm /etc/rsyslog.d/papertrail.conf
); else (
    sed -i "s/{PAPERTRAIL_SOCKET}/${PAPERTRAIL_SOCKET}/g" /etc/rsyslog.d/papertrail.conf
    sed -i "s/{PROJECT_NAME}/${PROJECT_NAME}/g" /etc/rsyslog.d/papertrail.conf
); fi

rsyslogd -n -f /etc/rsyslog.conf
