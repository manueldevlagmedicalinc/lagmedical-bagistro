# Nginx Proxy Manager

Nginx Proxy Manager owns the public HTTP/HTTPS entrypoint, public domains, Let's Encrypt SSL certificates, and redirects.

Traffic flow:

```text
Internet or local browser
↓
Nginx Proxy Manager :80/:443
↓
lagmedical_nginx:80
↓
lagmedical_web:9000
↓
Laravel / Bagisto
```

---

## Start NPM

From the project root:

```bash
./lagctl npm:up
```

NPM admin panel:

```text
Local: http://127.0.0.1:81
Production: http://SERVER_IP:81
```

Default login for a new NPM install:

```text
Email: admin@example.com
Password: changeme
```

Change the admin account immediately after first login.

---

## Backend Target

Use this backend in every Proxy Host:

```text
Scheme: http
Forward Hostname / IP: lagmedical_nginx
Forward Port: 80
```

Do not use these as NPM backend targets:

```text
localhost
127.0.0.1
lagmedical_web
nginx.lagmedicalinc.com
```

Reason: `lagmedical_nginx` is the Docker DNS name available inside the `lagmedical_proxy` network.

---

## Local Proxy Host

Use local `.test` domains for development/testing.

Example local domain:

```text
lagmedicalinc.test
```

### 1. Point Local Hostname To The Machine

On Windows, edit:

```text
C:\Windows\System32\drivers\etc\hosts
```

Add:

```text
127.0.0.1 lagmedicalinc.test
```

On Linux/WSL host environments, edit:

```text
/etc/hosts
```

Add:

```text
127.0.0.1 lagmedicalinc.test
```

### 2. Register Proxy Host In NPM

Open:

```text
http://127.0.0.1:81
```

Go to:

```text
Hosts -> Proxy Hosts -> Add Proxy Host
```

Tab `Detalles`:

```text
Nombres de Dominio:
lagmedicalinc.test

Esquema:
http

Nombre de Host / IP de Reenvío:
lagmedical_nginx

Puerto:
80

Lista de Acceso:
Accesible Públicamente

Cachear Recursos:
disabled

Bloquear Exploits Comunes:
enabled

Soporte de Websockets:
enabled
```

Tab `Ubicaciones Personalizadas`:

```text
No agregar nada.
```

Bagisto debe entrar por la raíz del dominio. No se necesita una ubicación personalizada para `/`, `/admin`, `/storage`, `/cache`, ni assets.

### 3. SSL For Local

Do not request Let's Encrypt for `.test` domains.

Let's Encrypt only works with public DNS domains reachable from the internet. For local `.test`, use HTTP or import a custom local certificate manually if needed.

### 3.1 Generate Local SSL Certificate With mkcert On Windows

Verify `mkcert` is installed:

```powershell
mkcert -version
```

Install the local trusted CA if it has not been installed yet:

```powershell
mkcert -install
```

Create the local certificate directory from the project root:

```powershell
mkdir C:\laragon\www\lagmedical\bagistro\docker\infra\npm\local-certs
```

Generate the local certificate:

```powershell
mkcert -cert-file "C:\laragon\www\lagmedical\bagistro\docker\infra\npm\local-certs\lagmedicalinc.test.pem" -key-file "C:\laragon\www\lagmedical\bagistro\docker\infra\npm\local-certs\lagmedicalinc.test-key.pem" lagmedicalinc.test localhost 127.0.0.1 ::1
```

Generated files:

```text
docker/infra/npm/local-certs/lagmedicalinc.test.pem
docker/infra/npm/local-certs/lagmedicalinc.test-key.pem
```

These files are ignored by Git.

Add the certificate in NPM:

```text
SSL Certificates -> Add SSL Certificate -> Custom
```

Use these files:

```text
Certificate Key:
docker/infra/npm/local-certs/lagmedicalinc.test-key.pem

Certificate:
docker/infra/npm/local-certs/lagmedicalinc.test.pem
```

Then edit the local Proxy Host and set the SSL tab:

```text
Certificado SSL:
Custom certificate for lagmedicalinc.test

Forzar SSL:
enabled

Soporte HTTP/2:
enabled

HSTS Habilitado:
disabled
```

Alternative local setup without HTTPS:

```text
Tab SSL:
Certificado SSL: None / Ninguno

Forzar SSL:
disabled

Soporte HTTP/2:
disabled

HSTS Habilitado:
disabled
```

If using local HTTP without a custom certificate, `.env` should use:

```env
APP_URL=http://lagmedicalinc.test
APP_HOST=lagmedicalinc.test
```

If using HTTPS locally with the mkcert custom certificate, `.env` should use:

```env
APP_URL=https://lagmedicalinc.test
APP_HOST=lagmedicalinc.test
```

### 4. Validate Local Host

Open:

```text
http://lagmedicalinc.test
```

Validate NPM API:

```bash
curl -I http://127.0.0.1:81/api/
```

Validate backend from NPM:

```bash
docker exec lagmedical_npm curl -I http://lagmedical_nginx:80
```

Expected result:

```text
HTTP/1.1 200 OK
```

---

## Production Proxy Host With SSL

Use real public domains in production.

Example:

```text
lagmedicalinc.com
www.lagmedicalinc.com
```

### 1. Point DNS To The Server

Before requesting SSL, DNS must point to the production server:

```text
lagmedicalinc.com      A -> SERVER_IP
www.lagmedicalinc.com  A -> SERVER_IP
```

For additional Bagisto channels:

```text
domain2.com      A -> SERVER_IP
www.domain2.com  A -> SERVER_IP
domain3.com      A -> SERVER_IP
www.domain3.com  A -> SERVER_IP
```

### 2. Open Required Ports

Public ports:

```text
80
443
```

NPM admin panel:

```text
81
```

Recommended: restrict port `81` by firewall, VPN, or IP allowlist.

### 3. Register Proxy Host In NPM

Open:

```text
http://SERVER_IP:81
```

Go to:

```text
Hosts -> Proxy Hosts -> Add Proxy Host
```

Tab `Detalles`:

```text
Nombres de Dominio:
lagmedicalinc.com
www.lagmedicalinc.com

Esquema:
http

Nombre de Host / IP de Reenvío:
lagmedical_nginx

Puerto:
80

Lista de Acceso:
Accesible Públicamente

Cachear Recursos:
disabled

Bloquear Exploits Comunes:
enabled

Soporte de Websockets:
enabled
```

Tab `Ubicaciones Personalizadas`:

```text
No agregar nada.
```

Bagisto debe recibir todas las rutas desde el mismo backend `lagmedical_nginx:80`.

Save the Proxy Host before requesting SSL if you want to validate HTTP routing first.

### 4. Validate HTTP Routing

Before SSL, test:

```text
http://lagmedicalinc.com
```

If it returns `502 Bad Gateway`, verify:

```bash
./lagctl ps
docker exec lagmedical_npm curl -I http://lagmedical_nginx:80
```

The backend test must return:

```text
HTTP/1.1 200 OK
```

### 5. Generate SSL Certificate

Edit the Proxy Host and open the SSL tab.

Tab `SSL`:

```text
Certificado SSL:
Request a new SSL Certificate

Forzar SSL:
enabled

Soporte HTTP/2:
enabled

HSTS Habilitado:
disabled initially

Dirección de Email:
admin email

Aceptar los términos de Let's Encrypt:
enabled
```

Save.

NPM will request a free Let's Encrypt certificate and store it under:

```text
docker/infra/npm/letsencrypt
```

### 6. Validate HTTPS

Open:

```text
https://lagmedicalinc.com
```

If `www` is configured in the same Proxy Host, also test:

```text
https://www.lagmedicalinc.com
```

Production `.env` should match the canonical domain:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://lagmedicalinc.com
APP_HOST=lagmedicalinc.com
```

---

## Canonical Domain

Recommended canonical domain:

```text
lagmedicalinc.com
```

Initial setup can keep both domains in the same Proxy Host:

```text
lagmedicalinc.com
www.lagmedicalinc.com
```

Later, if strict canonical redirects are required, create a Redirection Host:

```text
www.lagmedicalinc.com -> https://lagmedicalinc.com
```

---

## Additional Bagisto Channels

Create one Proxy Host per domain/channel.

Example channel 2:

```text
Domain Names:
domain2.com
www.domain2.com

Scheme:
http

Forward Hostname / IP:
lagmedical_nginx

Forward Port:
80
```

Generate SSL for each Proxy Host using the SSL tab.

All channels can forward to the same backend:

```text
lagmedical_nginx:80
```

Bagisto resolves the channel from the incoming `Host` header.

---

## Persistent Data

NPM runtime data is stored in:

```text
docker/infra/npm/data
docker/infra/npm/letsencrypt
```

These directories contain:

```text
NPM users
Proxy Hosts
SSL certificates
Let's Encrypt account data
NPM database
NPM logs
```

They are ignored by Git and must be backed up separately.

---

## Backup NPM

From the project root:

```bash
mkdir -p database/backups/local
tar -czf database/backups/local/npm-data.tar.gz docker/infra/npm/data docker/infra/npm/letsencrypt
```

Never commit this backup to Git.

---

## Restore NPM On Another Server

From the project root:

```bash
tar -xzf database/backups/local/npm-data.tar.gz -C .
./lagctl npm:up
```

Validate NPM API:

```bash
curl -I http://127.0.0.1:81/api/
```

Expected result:

```text
HTTP/1.1 200 OK
```

Validate backend from NPM:

```bash
docker exec lagmedical_npm curl -I http://lagmedical_nginx:80
```

Expected result:

```text
HTTP/1.1 200 OK
```

---

## First-Time Production Checklist

1. Point DNS records to the server IP.
2. Open ports `80` and `443`.
3. Protect port `81`.
4. Start the stack:

```bash
./lagctl build
```

5. Restore database and assets:

```bash
./lagctl db:fresh
```

6. Open NPM admin:

```text
http://SERVER_IP:81
```

7. Change admin login.
8. Create Proxy Host.
9. Validate HTTP routing.
10. Request Let's Encrypt certificate.
11. Enable Force SSL.
12. Test HTTPS domain.
