# Isolated environment with MyEtherWallet (MEW)

Environment
contains build of **MyEtherWallet** downloaded from GitHub
running in **Firefox** on **Ubuntu**
accessible via **VNC**.

Feel free to **DO NOT TRUST ANYONE**.
Check source files and build your own image from sources.
Switch DevTools to Network tab and keep eye on communication.
You can do whatever you wish with it. :)


## How to build

`docker build . --tag=local-mew`


## How to run

`docker run -ti -p 127.0.0.1:5901:5901 local-mew`

Feel free to change local port to whatever you wish - for example `53867`
(`... -p 127.0.0.1:53867:5901 ...`).
If you wish, you can add `e` option before image name `local-mew`, like:

* `-e MEW_VERSION=1.2.3` to change MEW version to *1.2.3*
* `-e VNC_PASSWORD=secret` to set up VNC password to *secret*
* `-e DISPLAY_WIDTH=1920` to change display/window width to *1920* px
* `-e DISPLAY_HEIGHT=1080` to change display and window height to *1080* px


## How to stop

You can simply **close all tabs in Firefox** or stop Docker container.

**WARNING:** If you only disconnects from VNC, everything keeps running, and you can return later.
It's a feature, not a bug.
