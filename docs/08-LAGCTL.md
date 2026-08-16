# LagCTL

Main commands

Docker

```bash
./lagctl build

./lagctl up

./lagctl down

./lagctl restart

./lagctl ps
```

Infrastructure

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

Laravel

```bash
./lagctl artisan

./lagctl composer

./lagctl npm
```

Database

```bash
./lagctl db:latest

./lagctl db:import

./lagctl db:export

./lagctl db:reset

./lagctl db:fresh

./lagctl db:shell
```

Utilities

```bash
./lagctl repair

./lagctl doctor
```

Backups

```bash
./lagctl backup:rclone-config

./lagctl backup:test

./lagctl backup:run

./lagctl backup:logs

./lagctl queue:work

./lagctl queue:restart

./lagctl mail:test manuel@lagmedicalinc.com
```
