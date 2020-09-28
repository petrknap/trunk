#!/usr/bin/env bash
set -e
set -x
DIR="${BASH_SOURCE%/*}"

docker build "${DIR}/.." --tag petrknap/selenium-needle
