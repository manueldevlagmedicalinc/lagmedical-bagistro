<?php

namespace App\Console\Commands;

use App\Mail\BackupFailedMail;
use App\Models\BackupLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class LagmedicalBackupDaily extends Command
{
    protected $signature = 'lagmedical:backup-daily
        {--skip-upload : Create local archives without uploading to rclone}
        {--log-id= : Existing backup log ID to update while running from queue}';

    protected $description = 'Create Lag Medical DB/assets/config/NPM backups, upload to Google Drive via rclone, and record the run.';

    public function handle(): int
    {
        $startedAt = now();

        $log = $this->backupLog($startedAt);

        $timestamp = $startedAt->format('Ymd_His');
        $localPath = config('lagmedical-backups.local_path');
        $runPath = $localPath.DIRECTORY_SEPARATOR.$timestamp;

        try {
            File::ensureDirectoryExists($runPath);
            $this->ensureRequiredPathsExist();

            $componentFiles = [
                $this->dumpDatabase($runPath),
                $this->archivePaths('assets', config('lagmedical-backups.paths.assets'), $runPath, 'assets.tar.gz'),
                $this->archivePaths('config', config('lagmedical-backups.paths.config'), $runPath, "config-{$timestamp}.tar.gz"),
                $this->archivePaths('npm', config('lagmedical-backups.paths.npm'), $runPath, "npm-{$timestamp}.tar.gz"),
            ];

            $componentFiles = array_values(array_filter($componentFiles));
            $backupFile = $this->archiveBackupBundle($componentFiles, $runPath, $timestamp);
            $sizeBytes = File::size($backupFile);

            $remotePath = $this->remoteRunPath($startedAt);

            if (! $this->option('skip-upload')) {
                $this->ensureRcloneConfigured();
                $remoteFilePath = $this->rcloneCopy($backupFile, $remotePath);

                $this->pruneRemoteBackups();

                File::deleteDirectory($runPath);
            }

            $this->pruneLocalBackups();

            $log->update([
                'status' => 'success',
                'monthly_snapshot' => false,
                'completed_at' => now(),
                'size_bytes' => $sizeBytes,
                'files' => [basename($backupFile)],
                'remote_path' => $this->option('skip-upload') ? null : $remoteFilePath,
                'remote_url' => $this->option('skip-upload') ? null : $this->driveSearchUrl(basename($backupFile)),
                'message' => $this->successMessage(),
            ]);

            $this->info('Backup completed successfully.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $log->update([
                'status' => 'failed',
                'completed_at' => now(),
                'message' => $exception->getMessage(),
            ]);

            $this->sendFailureEmail($log->fresh());

            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function backupLog($startedAt): BackupLog
    {
        if ($this->option('log-id')) {
            $log = BackupLog::query()->findOrFail((int) $this->option('log-id'));

            $log->update([
                'status' => 'running',
                'started_at' => $log->started_at ?: $startedAt,
                'message' => 'Backup started.',
            ]);

            return $log;
        }

        return BackupLog::create([
            'status' => 'running',
            'started_at' => $startedAt,
        ]);
    }

    private function ensureRequiredPathsExist(): void
    {
        $missingPaths = collect(config('lagmedical-backups.paths.required'))
            ->reject(fn (string $path) => File::exists(base_path($path)))
            ->values()
            ->all();

        if ($missingPaths !== []) {
            throw new \RuntimeException('Missing required backup paths: '.implode(', ', $missingPaths));
        }
    }

    private function sendFailureEmail(BackupLog $backupLog): void
    {
        try {
            Mail::to('manuel@lagmedicalinc.com')->send(new BackupFailedMail($backupLog));
        } catch (\Throwable $mailException) {
            $this->warn('Backup failure email could not be sent: '.$mailException->getMessage());
        }
    }

    private function dumpDatabase(string $runPath): string
    {
        $connection = config('lagmedical-backups.database.connection');
        $db = config("database.connections.{$connection}");

        if (($db['driver'] ?? null) !== 'mysql') {
            throw new \RuntimeException('Lag Medical backup currently supports only MySQL/MariaDB connections.');
        }

        $file = $runPath.DIRECTORY_SEPARATOR.'database-full.sql.gz';
        $command = sprintf(
            '%s --single-transaction --routines --triggers --host=%s --port=%s --user=%s %s | gzip > %s',
            escapeshellcmd(config('lagmedical-backups.database.mysqldump_binary')),
            escapeshellarg($db['host']),
            escapeshellarg((string) $db['port']),
            escapeshellarg($db['username']),
            escapeshellarg($db['database']),
            escapeshellarg($file),
        );

        $process = Process::fromShellCommandline($command, base_path(), [
            'MYSQL_PWD' => $db['password'] ?? '',
        ]);
        $process->setTimeout(900);
        $process->mustRun();

        return $file;
    }

    private function archivePaths(string $name, array $paths, string $runPath, string $filename): ?string
    {
        $existingPaths = collect($paths)
            ->filter(fn (string $path) => File::exists(base_path($path)))
            ->values()
            ->all();

        if ($existingPaths === []) {
            return null;
        }

        $file = $runPath.DIRECTORY_SEPARATOR.$filename;
        $process = new Process(array_merge(['tar', '-czf', $file], $existingPaths), base_path());
        $process->setTimeout(900);
        $process->mustRun();

        return $file;
    }

    private function archiveBackupBundle(array $componentFiles, string $runPath, string $timestamp): string
    {
        $file = $runPath.DIRECTORY_SEPARATOR."lagmedical-backup-{$timestamp}.zip";
        $process = new Process(array_merge(['zip', '-j', '-q', $file], $componentFiles), base_path());
        $process->setTimeout(900);
        $process->mustRun();

        return $file;
    }

    private function ensureRcloneConfigured(): void
    {
        $remote = config('lagmedical-backups.rclone_remote');

        if (! $remote) {
            throw new \RuntimeException('Set LAGMEDICAL_BACKUP_RCLONE_REMOTE in .env before running uploads.');
        }

        $this->runRcloneProcess(['version']);
    }

    private function rcloneCopy(string $source, string $destination): string
    {
        $remoteFilePath = $destination.'/'.basename($source);

        $this->runRcloneProcess(['mkdir', $destination]);
        $this->runRcloneProcess(['copyto', $source, $remoteFilePath]);

        return $remoteFilePath;
    }

    private function pruneRemoteBackups(): void
    {
        $retention = max(1, (int) config('lagmedical-backups.daily_retention_days'));

        $this->runRcloneProcess(['delete', $this->remotePath(), '--min-age', $retention.'d'], ignoreMissingDirectory: true);
    }

    private function pruneLocalBackups(): void
    {
        $cutoff = now()->subDays(max(1, (int) config('lagmedical-backups.daily_retention_days')));

        collect(File::directories(config('lagmedical-backups.local_path')))
            ->filter(fn (string $directory) => File::lastModified($directory) < $cutoff->getTimestamp())
            ->each(fn (string $directory) => File::deleteDirectory($directory));
    }

    private function remotePath(string $path = ''): string
    {
        $segments = array_values(array_filter([
            trim(config('lagmedical-backups.rclone_path'), '/'),
            trim($path, '/'),
        ]));

        return rtrim(config('lagmedical-backups.rclone_remote'), ':')
            .':'
            .implode('/', $segments);
    }

    private function remoteRunPath(\DateTimeInterface $startedAt): string
    {
        return $this->remotePath($startedAt->format('Y/m/d'));
    }

    private function driveSearchUrl(string $filename): string
    {
        return 'https://drive.google.com/drive/search?q='.rawurlencode($filename);
    }

    private function runProcess(Process $process): void
    {
        $process->setTimeout(900);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    private function runRcloneProcess(array $arguments, bool $ignoreMissingDirectory = false): void
    {
        $configPath = config('lagmedical-backups.rclone_config');
        $environment = [];

        if ($configPath) {
            File::ensureDirectoryExists(dirname($configPath));
            $environment['RCLONE_CONFIG'] = $configPath;
        }

        $process = new Process(array_merge(['rclone'], $arguments), base_path(), $environment);
        $process->setTimeout(900);
        $process->run();

        if ($process->isSuccessful()) {
            return;
        }

        if ($ignoreMissingDirectory && $this->isMissingDirectoryError($process)) {
            return;
        }

        throw new ProcessFailedException($process);
    }

    private function isMissingDirectoryError(Process $process): bool
    {
        $output = strtolower($process->getErrorOutput().$process->getOutput());

        return str_contains($output, 'directory not found')
            || str_contains($output, 'couldn\'t find directory')
            || str_contains($output, 'object not found');
    }

    private function successMessage(): string
    {
        if ($this->option('skip-upload')) {
            return 'Local backup created.';
        }

        return 'Backup uploaded.';
    }
}
