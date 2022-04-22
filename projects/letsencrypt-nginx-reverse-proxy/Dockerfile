ARG PROJECT_VERSION=1.3.0
ARG NGINX_VERSION=1.19

FROM nginx:${NGINX_VERSION}

RUN apt update \
    && apt install -y \
        openssl \
        certbot \
    && openssl req -x509 -nodes -days 36500 -newkey rsa:4096 -keyout /selfsigned.key -out /selfsigned.crt \
        -subj "/O=Petr Knap/CN=petrknap.cz/" \
    && apt clean \
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
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;\
    proxy_set_header Range $http_range;\
    proxy_set_header If-Range $http_if_range;\
    proxy_set_header X-Real-IP $remote_addr;\
'
ENV PROXY_ADDITIONAL_OPTIONS=''
ENV UPSTREAMS='\
#   upstream my-load-balancer {\
#        server 127.0.0.1:8001;\
#        server 127.0.0.1:8002;\
#        sticky cookie srv_id expires=1h domain=.example.com path=/;\
#   } \
'
ENV DEFAULT_SERVER='\
    return 404;\
'

CMD bash /command.bash

HEALTHCHECK --interval=15s --timeout=2s --retries=3 CMD curl --fail http://localhost/

EXPOSE 80
EXPOSE 443
