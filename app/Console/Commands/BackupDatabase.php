<?php

namespace App\Console\Commands;

use App\Services\DatabaseBackupService;
use Illuminate\Console\Command;
use Throwable;

class BackupDatabase extends Command
{
    protected $signature = 'database:backup {--keep= : Días que deben conservarse los respaldos}';

    protected $description = 'Crea un respaldo comprimido de la base de datos y elimina copias vencidas';

    public function handle(DatabaseBackupService $backup): int
    {
        try {
            $keep = $this->option('keep');
            if ($keep !== null && (! ctype_digit((string) $keep) || (int) $keep < 1)) {
                $this->error('La opción --keep debe ser un número entero mayor que cero.');

                return self::INVALID;
            }

            $path = $backup->create();
            $deleted = $backup->prune($keep === null ? null : (int) $keep);
            $this->info('Respaldo creado correctamente: '.$path);
            $this->line("Respaldos vencidos eliminados: {$deleted}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $this->error('No fue posible crear el respaldo: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
