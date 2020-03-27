# HTTP/HTTPS reverse proxy based on NGINX and Let's Encrypt

Simple HTTP proxy based on NGINX which automatically does this for you:
1. redirects from HTTP to HTTPS
1. obtains needed certificates vie Let's Encrypt
1. renews near-to-expire certificates

It's configured vie `RULES` variable which uses format `{domain}>{host[:port]}` separated by `,`.

## Example for Kubernetes

```yaml
apiVersion: apps/v1
kind: StatefulSet
metadata:
  name: reverse-proxy
  labels:
    app: reverse-proxy
spec:
  replicas: 1
  selector:
    matchLabels:
      app: reverse-proxy
  template:
    metadata:
      labels:
        app: reverse-proxy
    spec:
      containers:
        - name: reverse-proxy
          image: petrknap/letsencrypt-nginx-reverse-proxy:latest
          env:
            - name: RULES
              value: petrknap.cz>web-apache,mail.petrknap.cz>mail-nginx
          volumeMounts:
            - name: reverse-proxy-letsencrypt
              mountPath: /etc/letsencrypt
  volumeClaimTemplates:
    - metadata:
        name: reverse-proxy-letsencrypt
      spec:
        accessModes:
          - ReadWriteOnce
        resources:
          requests:
            storage: 1Gi # it needs only few MiBs but providers usually don't allow to allocate less than 1 GiB
```

```yaml
apiVersion: v1
kind: Service
metadata:
  name: catalog-reverse-proxy
  labels:
    app: catalog
    service: reverse-proxy
spec:
  selector:
    app: catalog
    service: reverse-proxy
  ports:
    - name: http
      protocol: TCP
      port: 80
    - name: https
      protocol: TCP
      port: 443
  externalIPs:
    - 1.2.3.4
```
