FROM debian:stable-slim

ENV LISTENING_PORT=7654

EXPOSE ${LISTENING_PORT}

RUN apt update && \
    apt install -y \
        n2n \
    && \
    apt clean && \
    rm -rf /var/lib/apt/lists/*

CMD /usr/bin/supernode -l ${LISTENING_PORT}
