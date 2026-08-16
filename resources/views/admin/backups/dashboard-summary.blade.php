@php
    use App\Models\BackupLog;

    $backupStats = [
        'total' => BackupLog::query()->count(),
        'success' => BackupLog::query()->where('status', 'success')->count(),
        'failed' => BackupLog::query()->where('status', 'failed')->count(),
        'running' => BackupLog::query()->whereIn('status', ['queued', 'running'])->count(),
    ];

    $latestBackup = BackupLog::query()->latest('started_at')->first();
@endphp

<div class="flex flex-col gap-2">
    <div class="flex items-center justify-between gap-3 max-sm:flex-wrap">
        <p class="text-base font-semibold text-gray-600 dark:text-gray-300">
            Backup Status
        </p>

        <a
            href="{{ route('admin.settings.backups.index') }}"
            class="text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400"
        >
            View all backups
        </a>
    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a
            href="{{ route('admin.settings.backups.index') }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Total Backups</span>
                <span class="grid h-8 min-w-12 place-items-center rounded-full border border-gray-300 px-2 text-xs font-semibold text-gray-700 transition group-hover:border-blue-500 group-hover:text-blue-600 dark:border-gray-700 dark:text-gray-300">View</span>
            </div>

            <p class="mt-4 text-6xl font-bold leading-none text-gray-900 dark:text-white">{{ $backupStats['total'] }}</p>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                All recorded backup runs
            </p>
        </a>

        <a
            href="{{ route('admin.settings.backups.index', ['status' => 'success']) }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Successful</span>
                <span class="grid h-8 min-w-12 place-items-center rounded-full border border-gray-300 px-2 text-xs font-semibold text-gray-700 transition group-hover:border-blue-500 group-hover:text-blue-600 dark:border-gray-700 dark:text-gray-300">View</span>
            </div>

            <p class="mt-4 text-6xl font-bold leading-none text-gray-900 dark:text-white">{{ $backupStats['success'] }}</p>

            <p class="mt-3 text-xs text-emerald-600 dark:text-emerald-400">
                Uploaded or locally completed
            </p>
        </a>

        <a
            href="{{ route('admin.settings.backups.index', ['status' => 'failed']) }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Failed</span>
                <span class="grid h-8 min-w-12 place-items-center rounded-full border border-gray-300 px-2 text-xs font-semibold text-gray-700 transition group-hover:border-blue-500 group-hover:text-blue-600 dark:border-gray-700 dark:text-gray-300">View</span>
            </div>

            <p class="mt-4 text-6xl font-bold leading-none text-gray-900 dark:text-white">{{ $backupStats['failed'] }}</p>

            <p class="mt-3 text-xs text-red-600 dark:text-red-400">
                Runs requiring attention
            </p>
        </a>

        <a
            href="{{ route('admin.settings.backups.index', ['status' => 'queued']) }}"
            class="group rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="flex items-start justify-between gap-3">
                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">Queued / Running</span>
                <span class="grid h-8 min-w-12 place-items-center rounded-full border border-gray-300 px-2 text-xs font-semibold text-gray-700 transition group-hover:border-blue-500 group-hover:text-blue-600 dark:border-gray-700 dark:text-gray-300">View</span>
            </div>

            <p class="mt-4 text-6xl font-bold leading-none text-gray-900 dark:text-white">{{ $backupStats['running'] }}</p>

            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Latest: {{ $latestBackup?->started_at?->format('Y-m-d H:i') ?? 'none' }}
            </p>
        </a>
    </div>
</div>
