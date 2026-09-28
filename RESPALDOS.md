# Respaldos de SIMRH

El sistema crea una copia comprimida de MySQL todos los días a las **2:00 a. m.** y conserva, de forma predeterminada, los últimos **30 días**. Los archivos se guardan en `storage/app/private/database-backups`, fuera del acceso público del navegador.

## Crear y revisar una copia manual

```powershell
php artisan database:backup
php artisan schedule:list
```

Para cambiar la retención de una ejecución concreta:

```powershell
php artisan database:backup --keep=60
```

En el servidor debe permanecer activo el programador de Laravel mediante una tarea de Windows que ejecute `php artisan schedule:run` cada minuto. Puede configurar estos valores en `.env` cuando sea necesario:

```text
BACKUP_RETENTION_DAYS=30
BACKUP_TIMEZONE=America/Costa_Rica
MYSQLDUMP_BINARY="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump.exe"
BACKUP_TIMEOUT_SECONDS=600
```

## Restauración segura

1. Ponga el sistema en mantenimiento con `php artisan down`.
2. Conserve la base actual y cree una base vacía para probar la restauración.
3. Descomprima el archivo `.sql.gz` seleccionado.
4. Importe el `.sql` desde MySQL Workbench mediante **Server > Data Import > Import from Self-Contained File**.
5. Apunte temporalmente la configuración a la base restaurada y ejecute `php artisan migrate:status`.
6. Compruebe usuarios, clientes, órdenes, movimientos de inventario y facturas.
7. Cuando la verificación sea correcta, utilice la base restaurada y ejecute `php artisan up`.

No restaure una copia directamente sobre la única base de producción. Mantenga además una copia fuera del equipo del taller para cubrir daños o pérdida del disco.
