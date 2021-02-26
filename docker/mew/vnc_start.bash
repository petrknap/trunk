#!/usr/bin/env bash
set -e

Xvfb "${DISPLAY}" -screen 0 "${DISPLAY_WIDTH}x${DISPLAY_HEIGHT}x16" &
if [[ "${VNC_PASSWORD}" == "" ]]; then (
  x11vnc -display "${DISPLAY}" -N -forever
); else (
  x11vnc -passwd "${VNC_PASSWORD}" -display "${DISPLAY}" -N -forever
); fi
