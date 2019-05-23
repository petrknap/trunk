#!/bin/bash
CONTAINER_NAME=$(basename $(pwd))

cat Dockerfile > /tmp/Dockerfile

sudo docker build -f /tmp/Dockerfile -t $CONTAINER_NAME .

rm /tmp/Dockerfile
