# Production Backups To Google Drive

This guide configures automated production backups for Bagisto/Lag Medical using `rclone` and Google Drive.

Backups are uploaded to:

```text
My Drive/Lag_Medical/01_IT/Servidores/Backups/YYYY/MM/DD/lagmedical-backup-YYYYMMDD_HHMMSS.zip
```

Each uploaded zip contains:

```text
database-full.sql.gz
assets.tar.gz
config-YYYYMMDD_HHMMSS.tar.gz
npm-YYYYMMDD_HHMMSS.tar.gz
```

After a successful upload, the local run folder is deleted. Failed runs and `--skip-upload` test runs remain local until local retention removes them.

---

## 1. Pull Code And Rebuild PHP Container

The PHP image includes `rclone`, so rebuild after deploying this implementation:

```bash
cd /var/www/lagmedicalinc/bagistro
git pull
./lagctl build
```

Validate `rclone` exists inside the PHP container:

```bash
docker exec lagmedical_web rclone version
```

---

## 2. Configure Production `.env`

Add or update these values in production `.env`:

```dotenv
LAGMEDICAL_BACKUP_LOCAL_PATH=
LAGMEDICAL_BACKUP_RCLONE_REMOTE=gdrive
LAGMEDICAL_BACKUP_RCLONE_CONFIG=
LAGMEDICAL_BACKUP_RCLONE_PATH=Lag_Medical/01_IT/Servidores/Backups
LAGMEDICAL_BACKUP_DAILY_RETENTION_DAYS=10
LAGMEDICAL_BACKUP_REQUIRED_PATHS=.env,.lagctl.env,docker/infra/npm/data,docker/infra/npm/letsencrypt
LAGMEDICAL_BACKUP_ASSET_PATHS=storage/app/public,public/storage
LAGMEDICAL_BACKUP_CONFIG_PATHS=.env,.lagctl.env,docker-compose.yml,docker-compose.infra.yml,docker/mariadb,docker/nginx,docker/php,docker/scripts,lagctl
LAGMEDICAL_BACKUP_NPM_PATHS=docker/infra/npm/data,docker/infra/npm/letsencrypt
MYSQLDUMP_BINARY=mysqldump

QUEUE_CONNECTION=database
DB_QUEUE_RETRY_AFTER=4000
```

Notes:

- `LAGMEDICAL_BACKUP_LOCAL_PATH=` stays empty to use the secure default: `storage/app/private/lagmedical-backups`.
- `LAGMEDICAL_BACKUP_RCLONE_CONFIG=` stays empty to use the secure default: `storage/app/private/rclone/rclone.conf`.
- `LAGMEDICAL_BACKUP_RCLONE_REMOTE=gdrive` must match the remote name created in `rclone config`.
- The Google Drive folder name is `Lag_Medical` with underscore.
- `QUEUE_CONNECTION=database` is required for the admin `Run Backup Now` button to run in background.
- `DB_QUEUE_RETRY_AFTER=4000` should be greater than the maximum expected backup runtime.
- `LAGMEDICAL_BACKUP_REQUIRED_PATHS` forces the backup to fail if critical private config or NPM runtime folders are missing.

Reload Laravel config after editing `.env`:

```bash
./lagctl artisan optimize:clear
```

Optional production config cache:

```bash
./lagctl artisan config:cache
```

If you later change `.env`, run `optimize:clear` and `config:cache` again.

---

## 3. Run Database Migrations

Run migrations for backup logs and remote Drive URL tracking:

```bash
./lagctl artisan migrate
```

Validate the table and URL column exist:

```bash
./lagctl artisan tinker --execute="dump(Schema::hasTable('lagmedical_backup_logs'), Schema::hasColumn('lagmedical_backup_logs', 'remote_url'));"
```

Expected:

```text
true
true
```

---

## 4. Configure Google Drive With `rclone`

Start `rclone` config inside the PHP container:

```bash
./lagctl backup:rclone-config
```

Use these answers:

```text
n) New remote
name> gdrive
Storage> Google Drive / drive
client_id> press Enter
client_secret> press Enter
scope> 1
root_folder_id> press Enter
service_account_file> press Enter
Edit advanced config?> n
Use auto config?> n
```

When `rclone` asks for `config_token`, generate it on a machine with a browser.

If local `rclone` is installed:

```bash
rclone authorize "drive"
```

If using a newer `rclone` version and the server gives a scoped command, run the exact command it prints.

After authorizing Google Drive in the browser, copy the full JSON token and paste it into the production terminal at:

```text
config_token>
```

Then answer:

```text
Configure this as a Shared Drive?> n
Keep this "gdrive" remote?> y
q) Quit config
```

The secret token is stored at:

```text
storage/app/private/rclone/rclone.conf
```

This path is ignored by Git and must never be committed or shared.

Recommended permissions:

```bash
chmod 600 storage/app/private/rclone/rclone.conf
```

---

## 5. Validate Docker HTTPS And Drive Access

Validate HTTPS works inside the PHP container:

```bash
docker exec lagmedical_web bash -lc 'timeout 15 curl -I https://google.com; printf "EXIT:%s\n" "$?"'
```

Expected:

```text
EXIT:0
```

Validate the `gdrive` remote exists:

```bash
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf rclone listremotes'
```

Expected:

```text
gdrive:
```

Validate Drive path access:

```bash
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf timeout 30 rclone lsf "gdrive:Lag_Medical/01_IT/Servidores/Backups"; printf "EXIT:%s\n" "$?"'
```

Expected:

```text
EXIT:0
```

If HTTPS from containers hangs, confirm Docker bridge MTU is applied:

```bash
docker network inspect bagistro_lagmedical --format '{{json .Options}}'
```

Expected:

```json
{"com.docker.network.driver.mtu":"1400"}
```

If the network was created before the MTU change:

```bash
./lagctl down
docker network rm bagistro_lagmedical
./lagctl up
```

---

## 6. Run A Local Backup Test

This creates the zip locally without uploading to Drive:

```bash
./lagctl backup:test
```

Validate Bagisto mail delivery for backup failure alerts:

```bash
./lagctl mail:test manuel@lagmedicalinc.com
```

This uses the configured default mailer, normally `bagisto-dynamic-smtp`.

Inspect recent logs:

```bash
./lagctl backup:logs
```

The test backup remains under:

```text
storage/app/private/lagmedical-backups
```

---

## 7. Run A Real Upload Backup

Run the real Google Drive upload:

```bash
./lagctl backup:run
```

Expected:

```text
Backup completed successfully.
```

Verify the file exists in Drive for today's date:

```bash
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf rclone lsf "gdrive:Lag_Medical/01_IT/Servidores/Backups/$(date +%Y)/$(date +%m)/$(date +%d)"'
```

Expected filename format:

```text
lagmedical-backup-YYYYMMDD_HHMMSS.zip
```

Verify database log includes `remote_path` and `remote_url`:

```bash
./lagctl backup:logs
```

---

## 8. Admin Validation

Open the backup log page:

```text
/{APP_ADMIN_URL}/settings/backups
```

Available features:

- Search by file name, remote path, or message.
- Filter by status.
- Filter by date range.
- Open successful uploaded backups in Google Drive using `Open in Drive`.

The dashboard also includes backup KPI cards at the end of the dashboard content:

- Total Backups
- Successful
- Failed
- Running

Each KPI card links to the backup log page.

---

## 9. Scheduler/Cron

The Laravel scheduler is registered in `bootstrap/app.php`:

```text
lagmedical:backup-daily daily at 02:15
```

Install host cron for Docker production:

```bash
* * * * * cd /var/www/lagmedicalinc/bagistro && docker exec lagmedical_web php artisan schedule:run >> /dev/null 2>&1
```

Validate scheduled commands:

```bash
./lagctl artisan schedule:list
```

---

## 10. Queue Worker For Admin Button

The `Run Backup Now` button in `Settings > Backups` does not run the backup inside the browser request. It creates a backup log with status `queued` and dispatches a Laravel queued job.

The queue worker must be running for queued backups to execute:

```bash
./lagctl queue:work
```

Equivalent container command:

```bash
docker exec lagmedical_web php artisan queue:work database --queue=backups,default --sleep=3 --tries=1 --timeout=3600
```

For production, keep the worker alive with Supervisor or systemd on the host. Do not rely on an interactive SSH terminal.

When deploying new code, restart workers gracefully:

```bash
./lagctl queue:restart
```

If the worker is not running, admin-triggered backups stay in `queued` status until a worker starts.

---

## 11. Cleanup Old Test Folders In Drive

During setup, old test folders may have been created, such as:

```text
gdrive:lagmedical
gdrive:Lag Medical
```

List before deleting:

```bash
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf rclone lsf gdrive:'
```

Delete only confirmed test folders:

```bash
docker exec lagmedical_web bash -lc 'RCLONE_CONFIG=storage/app/private/rclone/rclone.conf rclone purge "gdrive:lagmedical"'
```

Do not delete:

```text
gdrive:Lag_Medical/01_IT/Servidores/Backups
```

---

## 12. Production Command Summary

```bash
cd /var/www/lagmedicalinc/bagistro
git pull
./lagctl build
./lagctl artisan migrate
./lagctl artisan optimize:clear
./lagctl artisan config:cache
./lagctl backup:rclone-config
./lagctl backup:test
./lagctl backup:run
./lagctl backup:logs
./lagctl queue:work
```
