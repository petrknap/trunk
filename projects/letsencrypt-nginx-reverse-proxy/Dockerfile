FROM nginx:1.17

ENV RULES='1.example.com>127.0.0.1:8001,2.example.com>127.0.0.1:8002'

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
    certbot-auto --version --non-interactive

COPY prepare_nginx.bash /prepare_nginx.bash
RUN chmod +x /prepare_nginx.bash

COPY call_letsencrypt.bash /call_letsencrypt.bash
RUN chmod +x /call_letsencrypt.bash

COPY command.bash /command.bash
RUN chmod +x /command.bash

CMD bash /command.bash
