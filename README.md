# Lag Medical Inc

Enterprise B2B Ophthalmic Equipment Platform

---

## Development Environment

This project runs entirely in Docker using WSL2.

Main technologies

- PHP 8.3
- Laravel
- Bagisto
- MariaDB 10.11
- Redis
- Nginx
- Nginx Proxy Manager
- Mailpit

---

## Installation

```bash
git clone <repository>

cd bagistro

./lagctl build

./lagctl db:fresh
```

---

## Documentation

See the docs folder.

- docs/01-INSTALL.md
- docs/02-DOCKER.md
- docs/03-DATABASE.md
- docs/04-DEVELOPMENT.md
- docs/05-DEPLOYMENT.md
- docs/06-TROUBLESHOOTING.md
- docs/07-SERVER-ARCHITECTURE.md
- docs/08-LAGCTL.md
- docs/09-BACKUPS.md


cp .lagctl.env.example .lagctl.env
./lagctl build
./lagctl db:fresh
./lagctl repair