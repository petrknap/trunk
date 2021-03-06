#!/usr/bin/env bash
set -e

firefox \
  --safe-mode \
  --devtools \
  --width "${DISPLAY_WIDTH}" \
  --height "${DISPLAY_HEIGHT}" \
  "file://$(/mew_install.bash)/index.html#/access-my-wallet"
