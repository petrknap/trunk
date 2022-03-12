FROM node:17-slim

ENV LISTENING_PORT=1900

EXPOSE ${LISTENING_PORT}

WORKDIR /ssdp-faker/

COPY package.json ssdp-faker.js ./

RUN npm install

ENTRYPOINT ["node", "ssdp-faker.js"]
