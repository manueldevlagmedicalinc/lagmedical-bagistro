# Troubleshooting

Clear cache

```bash
./lagctl artisan optimize:clear
```

Restart Docker

```bash
./lagctl restart
```

Database

```bash
./lagctl db:shell
```

Recreate database

```bash
./lagctl db:fresh
```

Check environment

```bash
./lagctl doctor
```