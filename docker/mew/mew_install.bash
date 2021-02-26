#!/usr/bin/env bash
set -e

SOURCE="https://github.com/MyEtherWallet/MyEtherWallet/releases/download/v${MEW_VERSION}/MyEtherWallet-v${MEW_VERSION}.zip"
TARGET="/tmp/MyEtherWallet-v${MEW_VERSION}"

if [[ ! -e "${TARGET}" ]]; then (
  curl "${SOURCE}" --location --output "${TARGET}.zip" >&2
  unzip "${TARGET}.zip" -d "${TARGET}" >&2
); fi

echo "${TARGET}"
