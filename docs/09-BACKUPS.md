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

Always add

```
database/backups/local/*.sql
database/backups/local/*.gz
```

to

```
.gitignore
```