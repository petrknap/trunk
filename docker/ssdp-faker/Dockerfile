ARG SSDP_FAKER_VERSION=1.0.0
ARG NODE_VERSION=17

FROM node:${NODE_VERSION}-alpine

ARG SSDP_FAKER_VERSION

WORKDIR /ssdp-faker/

EXPOSE 1900/udp

RUN wget "https://github.com/petrknap/ssdp-faker/archive/refs/tags/v${SSDP_FAKER_VERSION}.tar.gz" -O - | tar -xz --strip-components=1 \
 && npm install \
;

ENTRYPOINT ["node", "ssdp-faker.js"]
