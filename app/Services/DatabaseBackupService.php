<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class DatabaseBackupService
{
    public function create(): string
    {
        $connectionName = (string) config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'] ?? null;

        return match ($driver) {
            'mysql', 'mariadb' => $this->createMySqlBackup($connection),
            'sqlite' => $this->createSqliteBackup($connection),
            default => throw new RuntimeException("El motor de base de datos {$driver} no está soportado para respaldos automáticos."),
        };
    }

    public function prune(?int $retentionDays = null): int
    {
        $days = $retentionDays ?? (int) config('backup.retention_days', 30);
        if ($days < 1) {
            throw new RuntimeException('La retención debe ser de al menos un día.');
        }

        $disk = Storage::disk(config('backup.disk', 'local'));
        $directory = trim((string) config('backup.directory', 'database-backups'), '/');
        $threshold = now()->subDays($days)->timestamp;
        $deleted = 0;

        foreach ($disk->files($directory) as $file) {
            if (str_ends_with($file, '.sql.gz') && $disk->lastModified($file) < $threshold && $disk->delete($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function createMySqlBackup(array $connection): string
    {
        $database = (string) ($connection['database'] ?? '');
        if ($database === '') {
            throw new RuntimeException('No se encontró el nombre de la base de datos configurada.');
        }

        [$relativePath, $absolutePath] = $this->destination($database);
        $stream = gzopen($absolutePath, 'wb9');
        if ($stream === false) {
            throw new RuntimeException('No fue posible crear el archivo comprimido del respaldo.');
        }

        $command = [
            (string) config('backup.mysqldump_binary', 'mysqldump'),
            '--host='.(string) ($connection['host'] ?? '127.0.0.1'),
            '--port='.(string) ($connection['port'] ?? '3306'),
            '--user='.(string) ($connection['username'] ?? ''),
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--default-character-set='.(string) ($connection['charset'] ?? 'utf8mb4'),
            '--no-tablespaces',
            $database,
        ];

        $process = new Process($command, base_path(), ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
        $process->setTimeout((int) config('backup.timeout_seconds', 600));

        try {
            $process->run(function (string $type, string $buffer) use ($stream): void {
                if ($type === Process::OUT && gzwrite($stream, $buffer) === false) {
                    throw new RuntimeException('No fue posible escribir el respaldo comprimido.');
                }
            });
        } catch (Throwable $exception) {
            gzclose($stream);
            @unlink($absolutePath);
            throw new RuntimeException('Falló la ejecución del respaldo: '.$exception->getMessage(), previous: $exception);
        }

        gzclose($stream);
        if (! $process->isSuccessful()) {
            @unlink($absolutePath);
            throw new RuntimeException('mysqldump no pudo generar el respaldo: '.trim($process->getErrorOutput()));
        }

        return $relativePath;
    }

    private function createSqliteBackup(array $connection): string
    {
        $source = (string) ($connection['database'] ?? '');
        if ($source === '' || $source === ':memory:' || ! is_file($source)) {
            throw new RuntimeException('No se encontró el archivo SQLite que debe respaldarse.');
        }

        [$relativePath, $absolutePath] = $this->destination(pathinfo($source, PATHINFO_FILENAME));
        $input = fopen($source, 'rb');
        $output = gzopen($absolutePath, 'wb9');
        if ($input === false || $output === false) {
            throw new RuntimeException('No fue posible abrir los archivos necesarios para el respaldo SQLite.');
        }

        while (! feof($input)) {
            $buffer = fread($input, 1024 * 1024);
            if ($buffer === false || gzwrite($output, $buffer) === false) {
                fclose($input);
                gzclose($output);
                @unlink($absolutePath);
                throw new RuntimeException('No fue posible copiar la base de datos SQLite.');
            }
        }
        fclose($input);
        gzclose($output);

        return $relativePath;
    }

    private function destination(string $database): array
    {
        $disk = Storage::disk(config('backup.disk', 'local'));
        $directory = trim((string) config('backup.directory', 'database-backups'), '/');
        $disk->makeDirectory($directory);
        $safeDatabase = preg_replace('/[^A-Za-z0-9_-]/', '_', $database) ?: 'database';
        $filename = $safeDatabase.'_'.now(config('backup.timezone', 'America/Costa_Rica'))->format('Y-m-d_His').'_'.strtolower(str()->random(6)).'.sql.gz';
        $relativePath = $directory.'/'.$filename;

        return [$relativePath, $disk->path($relativePath)];
    }
}
