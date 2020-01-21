FROM debian:stable-slim

ENV LISTENING_PORT=7654

EXPOSE ${LISTENING_PORT}

RUN PACKAGES=" \
        n2n \
    "; set -x \
    && apt update \
    && apt install -y ${PACKAGES} \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

CMD /usr/bin/supernode -v -l ${LISTENING_PORT}
