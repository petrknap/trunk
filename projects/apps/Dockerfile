FROM monostream/nodejs-electron-packager

COPY package.json /tmp/electron/package.json

RUN cd /tmp/electron \
&& npm install \
&& electron-packager ./ --all \
&& cd /tmp \
&& rm -rf /tmp/electron
