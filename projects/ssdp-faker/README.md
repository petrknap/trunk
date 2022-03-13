# SSDP faker

Simple SSDP faker that allows you to forward SSDP services beyond the local network.


## DLNA forwarding over SSH (LAN - Internet - LAN)

Get USN(s) and location on real servers LAN:

```bash
node ssdp-faker.js scan-network

# {
#   EXT: '',
#   DATE: 'Fri, 11 Mar 2022 15:53:27 GMT',
#   'CACHE-CONTROL': 'max-age=7200',
#   ST: 'urn:schemas-upnp-org:device:MediaServer:1',
#   SERVER: 'Windows/10.0 UPnP/1.0 EmbyServer/4.5',
#   USN: 'uuid:041668bd-c89e-4be7-a3ff-0702c9ca35cf::urn:schemas-upnp-org:device:MediaServer:1',
#   'CONTENT-LENGTH': '0',
#   LOCATION: 'http://192.168.0.253:8096/dlna/041668bd-c89e-4be7-a3ff-0702c9ca35cf/description.xml'
# }
```

Create SSH tunnel and fake server on another LAN:

```bash
ssh -o ServerAliveInterval=60 -L 0.0.0.0:8200:127.0.0.1:8096 user@real-server.public -N &
node ssdp-faker.js run-server \
    http://192.168.0.168:8200/dlna/041668bd-c89e-4be7-a3ff-0702c9ca35cf/description.xml \
    uuid:041668bd-c89e-4be7-a3ff-0702c9ca35cf::urn:schemas-upnp-org:device:MediaServer:1

# Location set to 'http://192.168.0.168:8200/dlna/041668bd-c89e-4be7-a3ff-0702c9ca35cf/description.xml'
# USN 'uuid:041668bd-c89e-4be7-a3ff-0702c9ca35cf::urn:schemas-upnp-org:device:MediaServer:1' added
# Server started
```

Do not forget to change the location if you use a different IP or port on the thin client.
