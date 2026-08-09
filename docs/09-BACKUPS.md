# Backups

Latest backup

database/backups/local/database-full.sql

Create backup

```bash
./lagctl db:export
```

Restore latest backup

```bash
./lagctl db:fresh
```

Restore external backup

```bash
./lagctl db:fresh /path/backup.sql
```

---

Never commit SQL backups to Git.

---

## Nginx Proxy Manager Backup

NPM stores Proxy Hosts, users, Let's Encrypt certificates, and internal database files in:

```text
docker/infra/npm/data
docker/infra/npm/letsencrypt
```

Backup NPM:

```bash
mkdir -p database/backups/local
tar -czf database/backups/local/npm-data.tar.gz docker/infra/npm/data docker/infra/npm/letsencrypt
```

Restore NPM on another server:

```bash
tar -xzf database/backups/local/npm-data.tar.gz -C .
./lagctl npm:up
```

Validate:

```bash
curl -I http://127.0.0.1:81/api/
docker exec lagmedical_npm curl -I http://lagmedical_nginx:80
```

Never commit NPM backups or runtime data to Git.

Always add

```
database/backups/local/*.sql
database/backups/local/*.gz
```

to

```
.gitignore
```
