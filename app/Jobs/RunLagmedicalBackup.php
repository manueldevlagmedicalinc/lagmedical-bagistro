<?php

namespace App\Jobs;

use App\Models\BackupLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;

class RunLagmedicalBackup implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 1;

    public function __construct(public int $backupLogId) {}

    public function handle(): void
    {
        Artisan::call('lagmedical:backup-daily', [
            '--log-id' => $this->backupLogId,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        BackupLog::query()
            ->whereKey($this->backupLogId)
            ->update([
                'status' => 'failed',
                'completed_at' => now(),
                'message' => $exception->getMessage(),
            ]);
    }
}
