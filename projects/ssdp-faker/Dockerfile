ARG PROJECT_VERSION=1.0.1
ARG NODE_VERSION=17.9

FROM node:${NODE_VERSION}-alpine

WORKDIR /ssdp-faker/

EXPOSE 1900/udp

ENTRYPOINT ["node", "ssdp-faker.js"]

COPY package.json ssdp-faker.js ./
RUN npm install
