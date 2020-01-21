# N2N supernode

## Start supernode

Run `docker run -d -p 7654:7654/udp petrknap/n2n-supernode`.

Or `docker run -d -p {[IP:]PORT}:7654/udp petrknap/n2n-supernode:{VERSION}`
where `{[IP:]PORT}` is optional IP address of listening interface and listening port
and `{VERSION}` is required version (for example `1`).

## Connect to running supernode

Run `edge -a 10.0.0.1 -c n2n -k key -l 127.0.0.1:7654`.

Or `edge -a {REQUESTED_IP} -c {NETWORK_NAME} -k {PRIVATE_KEY} -l {IP:PORT}`
where `{REQUESTED_IP}` is requested IP for this node,
`{NETWORK_NAME}` is name of your network,
`{PRIVATE_KEY}` is private key of your network and
`{IP:PORT}` is IP address of listening supernode interface and listening port.

---

Compilation of version 2 inspired by [zuijin/n2n](https://hub.docker.com/r/zuijin/n2n/dockerfile).
