FROM alpine:3.9

ENV PROJECT_NAME='petrknap.cz'
ENV PAPERTRAIL_SOCKET=''

EXPOSE 514
EXPOSE 4560

RUN apk add --no-cache rsyslog

COPY network.conf /etc/rsyslog.d/network.conf
COPY papertrail.conf /etc/rsyslog.d/papertrail.conf
COPY command.sh /command.sh

RUN chmod +x /command.sh

CMD /command.sh
