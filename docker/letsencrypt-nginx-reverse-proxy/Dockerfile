FROM nginx:latest

RUN apt update \
    && apt install -y \
        openssl \
        curl \
    && openssl req -x509 -nodes -days 36500 -newkey rsa:4096 -keyout /selfsigned.key -out /selfsigned.crt \
        -subj "/O=Petr Knap/CN=petrknap.cz/" \
    && curl -SL -o /tmp/certbot-auto https://dl.eff.org/certbot-auto \
    && mv /tmp/certbot-auto /usr/local/bin/certbot-auto \
    && chown root /usr/local/bin/certbot-auto \
    && chmod 0755 /usr/local/bin/certbot-auto \
    && certbot-auto --install-only --non-interactive \
    && apt clear \
    && rm  -rf \
        /var/lib/apt/lists/* \
        /tmp/* \
        /var/tmp/* \
;

COPY *.bash /
RUN chmod +x /*.bash

ENV IGNORE_LETS_ENCRYPT_ALL_ERRORS="false"
ENV IGNORE_LETS_ENCRYPT_OBTAIN_ERRORS="false"
ENV IGNORE_LETS_ENCRYPT_RENEW_ERRORS="true"
ENV RULES='1.example.com>127.0.0.1:8001,2.example.com>127.0.0.1:8002'
ENV PROXY_OPTIONS='\
    proxy_request_buffering off;\
'
ENV PROXY_HEADERS='\
    proxy_set_header Host $host;\
    proxy_set_header X-Forwarded-Host $host:$server_port;\
    proxy_set_header X-Forwarded-Proto $scheme;\
    proxy_set_header X-Real-IP $remote_addr;\
'
ENV PROXY_ADDITIONAL_OPTIONS=''

CMD bash /command.bash

EXPOSE 80
EXPOSE 443
