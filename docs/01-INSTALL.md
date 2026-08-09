# Installation

## Requirements

- Ubuntu, WSL2 Ubuntu, or compatible Linux host
- Docker Engine
- Docker Compose plugin
- Git
- SSH key with access to the repository

---

## Build From Zero In Test Environment

### GitHub SSH Key

Generate a server SSH key:

```bash
ssh-keygen -t ed25519 -C "server@lagmedicalinc.com" -f ~/.ssh/lagmedical_github
```

Start the SSH agent and register the key:

```bash
eval "$(ssh-agent -s)"
ssh-add ~/.ssh/lagmedical_github
```

Show the public key:

```bash
cat ~/.ssh/lagmedical_github.pub
```

Add that public key in GitHub:

```text
GitHub -> Repository -> Settings -> Deploy keys -> Add deploy key
```

Recommended settings:

```text
Title: lagmedical production server
Key: contents of ~/.ssh/lagmedical_github.pub
Allow write access: disabled
```

Create or update SSH config:

```bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
```

```bash
cat >> ~/.ssh/config <<'EOF'
Host github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/lagmedical_github
    IdentitiesOnly yes
EOF
```

Set secure permissions:

```bash
chmod 600 ~/.ssh/lagmedical_github ~/.ssh/config
chmod 644 ~/.ssh/lagmedical_github.pub
```

Test GitHub access:

```bash
ssh -T git@github.com
```

Expected result:

```text
You've successfully authenticated, but GitHub does not provide shell access.
```

Clone the repository:

```bash
git clone git@github.com:manueldevlagmedicalinc/lagmedical-bagistro.git bagistro
cd bagistro
```

Create the local LagCTL config:

```bash
cp .lagctl.env.example .lagctl.env
```

Create the local backup directory:

```bash
mkdir -p database/backups/local
```

Copy the private, non-tracked files into the project:

```text
.env
.lagctl.env
database/backups/local/database-full.sql.gz
database/backups/local/assets.tar.gz
```

Build and start the full stack:

```bash
./lagctl build
```

Restore database and assets:

```bash
./lagctl db:fresh
```

Repair Laravel storage/cache:

```bash
./lagctl repair
```

Check containers and environment health:

```bash
./lagctl ps
./lagctl doctor
```

---

## Access

Application:

```text
APP_URL from .env
```

Local default:

```text
https://lagmedicalinc.test
```

Nginx Proxy Manager admin:

```text
NPM_ADMIN_URL from .lagctl.env
```

Local default:

```text
http://127.0.0.1:81
```

Nginx Proxy Manager backend target:

```text
http://lagmedical_nginx:80
```

---

## Dev Tools

phpMyAdmin is dev-only and does not start by default.

Start phpMyAdmin:

```bash
./lagctl phpmyadmin:up
```

Stop phpMyAdmin:

```bash
./lagctl phpmyadmin:down
```

NPM logs:

```bash
./lagctl npm:logs
```

---

## Image Cache Validation

The PHP image must support WebP for Bagisto image cache.

```bash
docker exec lagmedical_web php83 -r "var_dump(function_exists('imagecreatefromwebp'), function_exists('imagewebp'));"
```

Expected result:

```text
bool(true)
bool(true)
```

If this returns `false`, rebuild the image after ensuring GD is compiled with WebP support.
