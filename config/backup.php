<?php

return [
    'disk' => env('BACKUP_DISK', 'local'),
    'directory' => env('BACKUP_DIRECTORY', 'database-backups'),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'timezone' => env('BACKUP_TIMEZONE', 'America/Costa_Rica'),
    'mysqldump_binary' => env('MYSQLDUMP_BINARY', 'mysqldump'),
    'timeout_seconds' => (int) env('BACKUP_TIMEOUT_SECONDS', 600),
];
