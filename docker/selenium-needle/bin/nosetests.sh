#!/usr/bin/env bash
set -e
# set -x # see running commands
DIR="${BASH_SOURCE%/*}"

HOST_IP="$(ip route get 8.8.8.8 | head -1 | awk '{ print $7 }')"

grep localhost /etc/hosts > /tmp/hosts
grep -v localhost /etc/hosts | sed -r "s/127\.[0-9]+\.[0-9]+\.[0-9]+/${HOST_IP}/g" >> /tmp/hosts
echo "${HOST_IP} host.local" >> /tmp/hosts

docker run -ti --rm \
  -e DISPLAY="${DISPLAY}" \
  -v /tmp/.X11-unix:/tmp/.X11-unix \
  -v /tmp/hosts:/etc/hosts \
  -v "$(pwd)":/mnt/nosetests \
  -u "$(id -u "${USER}")":"$(id -g "${USER}")" \
  petrknap/selenium-needle bash -c "cd /mnt/nosetests && (rm screenshots/*.png 2&>/dev/null || true) && nosetests $*" \
|| (cd screenshots && for FAIL in *.png; do (
  compare "${FAIL}" "baseline/${FAIL}" "${FAIL}.diff.png" || true
); done)
