<?php

return [
    'local_path' => env('LAGMEDICAL_BACKUP_LOCAL_PATH') ?: storage_path('app/private/lagmedical-backups'),

    'rclone_remote' => env('LAGMEDICAL_BACKUP_RCLONE_REMOTE', ''),

    'rclone_path' => trim(env('LAGMEDICAL_BACKUP_RCLONE_PATH', 'lagmedical/backups'), '/'),

    'rclone_config' => env('LAGMEDICAL_BACKUP_RCLONE_CONFIG') ?: storage_path('app/private/rclone/rclone.conf'),

    'daily_retention_days' => (int) env('LAGMEDICAL_BACKUP_DAILY_RETENTION_DAYS', 10),

    'database' => [
        'connection' => env('LAGMEDICAL_BACKUP_DB_CONNECTION', env('DB_CONNECTION', 'mysql')),
        'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    ],

    'paths' => [
        'required' => array_values(array_filter(array_map('trim', explode(',', env(
            'LAGMEDICAL_BACKUP_REQUIRED_PATHS',
            '.env,.lagctl.env,docker/infra/npm/data,docker/infra/npm/letsencrypt'
        ))))),

        'assets' => array_values(array_filter(array_map('trim', explode(',', env(
            'LAGMEDICAL_BACKUP_ASSET_PATHS',
            'storage/app/public,public/storage'
        ))))),

        'config' => array_values(array_filter(array_map('trim', explode(',', env(
            'LAGMEDICAL_BACKUP_CONFIG_PATHS',
            '.env,.lagctl.env,docker-compose.yml,docker-compose.infra.yml,docker/mariadb,docker/nginx,docker/php,docker/scripts,lagctl'
        ))))),

        'npm' => array_values(array_filter(array_map('trim', explode(',', env(
            'LAGMEDICAL_BACKUP_NPM_PATHS',
            'docker/infra/npm/data,docker/infra/npm/letsencrypt'
        ))))),
    ],
];
