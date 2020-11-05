FROM python:3

ENV SELENIUM_VERSION="3.14.1"
ENV NEEDLE_VERSION="0.5.0"
ENV OLEFILE_VERSION="0.46.0"
ENV GECKODRIVER_VERSION="0.26.0"
ENV FIREFOX_VERSION="68.12.0"

RUN cd /tmp \
 && apt update \
 && apt install -y \
    wget \
    firefox-esr="${FIREFOX_VERSION}esr-1~deb10u1" \
 && easy_install pip \
 && pip install selenium=="${SELENIUM_VERSION}" \
 && pip install needle=="${NEEDLE_VERSION}" \
 && pip install olefile=="${OLEFILE_VERSION}" \
\
 && echo "vvv Hack to prepare home at root" \
 && firefox -headless -createProfile default \
 && cp -r /root/.cache /.cache \
 && cp -r /root/.mozilla /.mozilla \
 && chmod 0777 -R /.cache /.mozilla \
 && echo "^^^ Hack to prepare home at root" \
\
 && echo "vvv Inspired by https://github.com/mozilla/geckodriver/issues/1600" \
 && GECKODRIVER_TAR_GZ="geckodriver-v${GECKODRIVER_VERSION}-linux64.tar.gz" \
 && wget "https://github.com/mozilla/geckodriver/releases/download/v${GECKODRIVER_VERSION}/${GECKODRIVER_TAR_GZ}" \
 && tar -xzf "${GECKODRIVER_TAR_GZ}" -C /usr/local/bin \
 && chmod +x /usr/local/bin/geckodriver \
 && rm "${GECKODRIVER_TAR_GZ}" \
 && echo "^^^ Inspired by https://github.com/mozilla/geckodriver/issues/1600" \
\
 && apt clean \
 && rm -rf \
    /var/lib/apt/lists/* \
    /tmp/* \
    /var/tmp/* \
;
