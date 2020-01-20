FROM alpine:latest

ENV LISTENING_PORT=7654

EXPOSE ${LISTENING_PORT}

RUN BUILD_DEPENDENCIES=" \
        build-base \
        git \
        linux-headers \
        openssl-dev \
    "; set -x \
    && apk add ${BUILD_DEPENDENCIES} \
    && cd /tmp \
    && git clone https://github.com/ntop/n2n.git n2n \
    && cd n2n \
    && git checkout 2.4-stable \
    && make supernode \
    && cp supernode /usr/bin/supernode \
    && apk del ${BUILD_DEPENDENCIES} \
    && rm -rf /var/cache/apk/* \
    && rm -rf /tmp/*

CMD /usr/bin/supernode -f -v -l ${LISTENING_PORT}
