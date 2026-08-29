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

---

## Automated Production Backups

The app includes a scheduled Artisan backup command:

```bash
php artisan lagmedical:backup-daily
```

It creates compressed archives for:

- MariaDB/MySQL database
- public assets from `storage/app/public` and `public/storage`
- Docker/app configuration files
- Nginx Proxy Manager data and Let's Encrypt certificates

It uploads backups to Google Drive through `rclone` and records every run in `lagmedical_backup_logs`.

When using Docker, prefer the `lagctl` wrappers:

```bash
./lagctl backup:test
./lagctl backup:run
./lagctl backup:logs
```

Admin logs are available at:

```text
/{APP_ADMIN_URL}/settings/backups
```

Successful uploaded backups show an `Open in Drive` button. The button opens Google Drive search for the exact timestamped backup zip file name.

Run migrations before enabling the scheduler:

```bash
php artisan migrate
```

`rclone` is installed in the PHP Docker image. Rebuild after pulling the Dockerfile change:

```bash
./lagctl build
```

Configure Google Drive inside the PHP container. The config is stored at `storage/app/private/rclone/rclone.conf`, so it survives container recreation:

```bash
./lagctl backup:rclone-config
```

Recommended remote name:

```text
gdrive
```

Production `.env` settings:

```dotenv
LAGMEDICAL_BACKUP_RCLONE_REMOTE=gdrive
LAGMEDICAL_BACKUP_RCLONE_CONFIG=
LAGMEDICAL_BACKUP_RCLONE_PATH=Lag_Medical/01_IT/Servidores/Backups
LAGMEDICAL_BACKUP_DAILY_RETENTION_DAYS=10
LAGMEDICAL_BACKUP_ASSET_PATHS=storage/app/public,public/storage
LAGMEDICAL_BACKUP_CONFIG_PATHS=.env,.lagctl.env,docker-compose.yml,docker-compose.infra.yml,docker/mariadb,docker/nginx,docker/php,docker/scripts,lagctl
LAGMEDICAL_BACKUP_NPM_PATHS=docker/infra/npm/data,docker/infra/npm/letsencrypt
MYSQLDUMP_BINARY=mysqldump
```

Validate without uploading:

```bash
./lagctl backup:test
```

Validate upload:

```bash
./lagctl backup:run
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf rclone lsf "gdrive:Lag_Medical/01_IT/Servidores/Backups/$(date +%Y)/$(date +%m)/$(date +%d)"'
```

Retention behavior:

- Remote backups older than `LAGMEDICAL_BACKUP_DAILY_RETENTION_DAYS` are deleted from the configured backup root.
- Each run uploads one timestamped zip file under `YYYY/MM/DD`.
- The zip contains `database-full.sql.gz`, `assets.tar.gz`, `config-YYYYMMDD_HHMMSS.tar.gz`, and `npm-YYYYMMDD_HHMMSS.tar.gz`.
- After a successful upload, the local run directory is deleted. Failed or `--skip-upload` runs remain local until local retention removes them.
- Local backup run directories under `storage/app/private/lagmedical-backups` follow the daily retention window.

Laravel scheduler entry is already registered in `bootstrap/app.php` for `02:15` daily. Ensure cron is installed on the host:

```bash
* * * * * cd /var/www/lagmedicalinc/bagistro && php artisan schedule:run >> /dev/null 2>&1
```

If running through Docker, use the app container command instead:

```bash
* * * * * cd /var/www/lagmedicalinc/bagistro && docker exec lagmedical_web php artisan schedule:run >> /dev/null 2>&1
```
