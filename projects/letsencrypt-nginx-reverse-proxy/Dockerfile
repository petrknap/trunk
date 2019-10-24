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
    chmod 0755 /usr/local/bin/certbot-auto

COPY init.sh /init.sh
RUN chmod +x /init.sh

COPY letsencrypt.sh /letsencrypt.sh
RUN chmod +x /letsencrypt.sh

COPY command.sh /command.sh
RUN chmod +x /command.sh

CMD /command.sh
