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
              value: petrknap.cz>web-nginx,mail.petrknap.cz>mail-nginx
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
            storage: 1Gi
```
