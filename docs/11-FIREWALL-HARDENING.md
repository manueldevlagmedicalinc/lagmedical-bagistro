# Firewall Hardening

Use this procedure after the stack is running and Nginx Proxy Manager is reachable through the admin subdomain.

Expected public ports:

```text
22   SSH
80   NPM HTTP / Let's Encrypt
443  NPM HTTPS
```

Ports that must not be public:

```text
81    NPM admin direct port
3306  MariaDB
3307  MariaDB host mapping
6379  Redis
8086  phpMyAdmin
9000  PHP-FPM
```

---

## 1. Validate Web Before Firewall

From the server:

```bash
curl -I https://lagmedicalinc.com
curl -I https://npm.lagmedicalinc.com
```

Both should respond before enabling the firewall.

---

## 2. Apply Docker Port Changes

MariaDB must not be exposed to the host. The `db` service must not publish `3307:3306`.

Apply the current compose configuration:

```bash
cd /var/www/lagmedicalinc/bagistro
./lagctl up
```

Validate Docker ports:

```bash
docker ps --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
```

Expected for MariaDB:

```text
lagmedical_db    3306/tcp
```

It must not show:

```text
0.0.0.0:3307->3306/tcp
```

Validate listening ports:

```bash
sudo ss -tulpn | grep LISTEN
```

There should be no public listener for `3306` or `3307`.

---

## 3. Configure UFW Rules

Do not enable UFW until SSH is allowed.

```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
```

Do not allow:

```text
81/tcp
3307/tcp
6379/tcp
8086/tcp
9000/tcp
```

---

## 4. Enable UFW

```bash
sudo ufw enable
```

Validate:

```bash
sudo ufw status verbose
```

Expected allowed ports:

```text
22/tcp
80/tcp
443/tcp
```

---

## 5. Final Validation

```bash
curl -I https://lagmedicalinc.com
curl -I https://npm.lagmedicalinc.com
docker ps --format "table {{.Names}}\t{{.Ports}}\t{{.Status}}"
sudo ss -tulpn | grep LISTEN
```

Expected public listeners:

```text
:22
:80
:443
```

If `:3307`, `:6379`, `:8086`, or `:9000` appears on `0.0.0.0` or `[::]`, stop and close that exposure before continuing.

---

## NPM Admin Subdomain

The NPM admin UI should be accessed through:

```text
https://npm.lagmedicalinc.com
```

The direct port should not be used publicly:

```text
http://SERVER_IP:81
```

The infrastructure compose binds the direct NPM admin port to localhost only:

```text
127.0.0.1:81:81
```

This keeps the admin backend available to NPM internally while avoiding a public `0.0.0.0:81` listener.

Keep NPM 2FA enabled and use a strong password.
