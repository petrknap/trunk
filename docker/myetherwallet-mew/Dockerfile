FROM ubuntu:20.04

ENV MEW_VERSION="5.7.21"

RUN apt-get update \
 && DEBIAN_FRONTEND=noninteractive apt-get install -y \
    curl \
    firefox \
    unzip \
    xvfb \
    x11vnc \
 && apt clean \
 && rm  -rf \
    /var/lib/apt/lists/* \
    /tmp/* \
    /var/tmp/* \
;

COPY *.bash /

RUN chmod +x /*.bash \
 && /mew_install.bash \
;

ENV VNC_PASSWORD=""
ENV VNC_PORT=5901
ENV DISPLAY=:1
ENV DISPLAY_WIDTH=1440
ENV DISPLAY_HEIGHT=810

EXPOSE ${VNC_PORT}

CMD bash /command.bash
