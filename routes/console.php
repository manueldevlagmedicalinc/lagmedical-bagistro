<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('lagmedical:seed-demo {--skip-backup : Run seeder without SQL backup}', function () {
    $connection = config('database.default');
    $db = config("database.connections.$connection");

    if (! $db || ($db['driver'] ?? null) !== 'mysql') {
        $this->error('This command currently supports only MySQL connections.');

        return 1;
    }

    if (! $this->option('skip-backup')) {
        $backupDir = storage_path('app/private/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Ymd_His');
        $backupFile = $backupDir.DIRECTORY_SEPARATOR."{$db['database']}_{$timestamp}.sql";

        $configuredBinary = env('MYSQLDUMP_BINARY');
        $binaryCandidates = array_filter([
            $configuredBinary,
            'mysqldump',
            'C:\\laragon\\bin\\mysql\\mysql-8.4.3-winx64\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\mysql-8.0.30-winx64\\bin\\mysqldump.exe',
        ]);

        $mysqldump = null;

        foreach ($binaryCandidates as $candidate) {
            try {
                $probe = new Process([$candidate, '--version']);
                $probe->setTimeout(8);
                $probe->run();

                if ($probe->isSuccessful()) {
                    $mysqldump = $candidate;

                    break;
                }
            } catch (\Throwable) {
                // Try next candidate.
            }
        }

        if (! $mysqldump) {
            $this->error('mysqldump binary not found. Set MYSQLDUMP_BINARY in .env with the full executable path.');

            return 1;
        }

        $this->info("Creating backup: {$backupFile}");

        $dumpCommand = [
            $mysqldump,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--host='.$db['host'],
            '--port='.(string) $db['port'],
            '--user='.$db['username'],
            '--password='.$db['password'],
            $db['database'],
        ];

        $dumpProcess = new Process($dumpCommand);
        $dumpProcess->setTimeout(300);
        $dumpProcess->run();

        if (! $dumpProcess->isSuccessful()) {
            $this->error('Backup failed. Seeder was not executed.');
            $this->line($dumpProcess->getErrorOutput() ?: $dumpProcess->getOutput());

            return 1;
        }

        File::put($backupFile, $dumpProcess->getOutput());
        $this->info('Backup completed successfully.');
    } else {
        $this->warn('Skipping backup as requested.');
    }

    $this->info('Running LagMedicalDemoSeederV2...');

    $exitCode = Artisan::call('db:seed', [
        '--class' => 'Database\\Seeders\\LagMedicalDemoSeederV2',
        '--force' => true,
    ]);

    $this->line(Artisan::output());

    if ($exitCode !== 0) {
        $this->error('Seeder execution failed.');

        return 1;
    }

    $this->info('LagMedical demo seed completed.');

    return 0;
})->purpose('Backup database into storage/private and run LagMedicalDemoSeederV2');
