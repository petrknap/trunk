#!/usr/bin/env bash
set -e
set -x
DIR="${BASH_SOURCE%/*}"

docker run -ti --rm \
  -e DISPLAY=$DISPLAY \
  -v /tmp/.X11-unix:/tmp/.X11-unix \
  -v "$(pwd)":"/mnt/nosetests" \
  -u "$(id -u "${USER}")":"$(id -g "${USER}")" \
  petrknap/selenium-needle bash -c "cd /mnt/nosetests && nosetests $*" \
;
