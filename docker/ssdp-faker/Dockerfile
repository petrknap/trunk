FROM node:17-alpine

WORKDIR /ssdp-faker/

ENV SSDP_FAKER_VERSION=1.0.0

EXPOSE 1900/udp


RUN wget https://github.com/petrknap/ssdp-faker/archive/refs/tags/v${SSDP_FAKER_VERSION}.tar.gz -O - | tar -xz --strip-components=1 \
 && ls -alh \
 && npm install \
;

ENTRYPOINT ["node", "ssdp-faker.js"]
