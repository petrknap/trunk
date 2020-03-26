FROM nginx:latest

ENV IGNORE_LETS_ENCRYPT_ERRORS="false"
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

VOLUME /etc/letsencrypt

EXPOSE 80
EXPOSE 443

RUN apt update && \
    apt install -y \
        openssl \
        curl \
;

RUN openssl req -x509 -nodes -days 36500 -newkey rsa:4096 -keyout /selfsigned.key -out /selfsigned.crt \
    -subj "/O=Petr Knap/CN=petrknap.cz/"

RUN curl -SL -o /tmp/certbot-auto https://dl.eff.org/certbot-auto && \
    mv /tmp/certbot-auto /usr/local/bin/certbot-auto && \
    chown root /usr/local/bin/certbot-auto && \
    chmod 0755 /usr/local/bin/certbot-auto && \
    certbot-auto --install-only --non-interactive

COPY prepare_nginx.bash /prepare_nginx.bash
RUN chmod +x /prepare_nginx.bash

COPY call_letsencrypt.bash /call_letsencrypt.bash
RUN chmod +x /call_letsencrypt.bash

COPY command.bash /command.bash
RUN chmod +x /command.bash

CMD bash /command.bash
