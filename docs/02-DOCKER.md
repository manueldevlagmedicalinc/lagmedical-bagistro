# Docker

Containers

- web
- nginx
- db
- redis

Dev-only containers

- phpmyadmin

Infrastructure

- nginx-proxy-manager

Nginx Proxy Manager owns public hosts and SSL certificates.

Docker commands

```bash
./lagctl build

./lagctl up

./lagctl down

./lagctl restart

./lagctl ps

./lagctl logs
```

Nginx Proxy Manager

```bash
./lagctl npm:up

./lagctl npm:down

./lagctl npm:logs
```

Dev tools

```bash
./lagctl phpmyadmin:up

./lagctl phpmyadmin:down
```
