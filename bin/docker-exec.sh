#!/bin/bash
CONTAINER_NAME=$(basename $(pwd))
_USER=$USER

sudo docker run -v $(pwd):/app --rm $CONTAINER_NAME bash -c "cd /app && $*"

sudo chown $_USER -R .
