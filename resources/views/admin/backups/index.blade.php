<x-admin::layouts>
    <x-slot:title>
        Backup Logs
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Backup Logs
        </p>

        <form
            method="POST"
            action="{{ route('admin.settings.backups.run') }}"
            onsubmit="return confirm('Queue a new backup now? The queue worker will run it in background.')"
        >
            @csrf

            <button type="submit" class="primary-button">
                Run Backup Now
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="mt-4 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mt-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700 dark:border-red-900 dark:bg-red-950 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    <form
        method="GET"
        action="{{ route('admin.settings.backups.index') }}"
        class="mt-4 rounded bg-white p-4 shadow dark:bg-gray-900"
    >
        <div class="grid gap-3 md:grid-cols-4">
            <div class="grid gap-1.5">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Search</label>

                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="File, path, message"
                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>

            <div class="grid gap-1.5">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Status</label>

                <select
                    name="status"
                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                >
                    <option value="">All statuses</option>
                    <option value="success" @selected(request('status') === 'success')>Success</option>
                    <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                    <option value="queued" @selected(request('status') === 'queued')>Queued</option>
                    <option value="running" @selected(request('status') === 'running')>Running</option>
                </select>
            </div>

            <div class="grid gap-1.5">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date From</label>

                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>

            <div class="grid gap-1.5">
                <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date To</label>

                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="flex min-h-[39px] w-full rounded-md border px-3 py-2 text-sm text-gray-600 transition-all hover:border-gray-400 focus:border-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300"
                >
            </div>
        </div>

        <div class="mt-3 flex gap-2">
            <button type="submit" class="primary-button justify-center">
                Search
            </button>

            <a href="{{ route('admin.settings.backups.index') }}" class="secondary-button justify-center">
                Reset
            </a>
        </div>
    </form>

    <div class="mt-4 overflow-x-auto rounded bg-white shadow dark:bg-gray-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b bg-gray-50 text-xs uppercase text-gray-600 dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300">
                <tr>
                    <th class="px-4 py-3">Started</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Size</th>
                    <th class="px-4 py-3">Remote Path</th>
                    <th class="px-4 py-3">Drive</th>
                    <th class="px-4 py-3">Files</th>
                    <th class="px-4 py-3">Message</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-gray-800">
                @forelse ($logs as $log)
                    <tr class="text-gray-700 dark:text-gray-200">
                        <td class="whitespace-nowrap px-4 py-3">{{ $log->started_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs font-semibold {{ $log->status === 'success' ? 'bg-green-100 text-green-700' : ($log->status === 'failed' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">{{ number_format($log->size_bytes / 1024 / 1024, 2) }} MB</td>
                        <td class="px-4 py-3">{{ $log->remote_path ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($log->remote_url)
                                <a
                                    href="{{ $log->remote_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="secondary-button"
                                >
                                    Open in Drive
                                </a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            {{ implode(', ', $log->files ?? []) ?: '-' }}
                        </td>
                        <td class="px-4 py-3">{{ $log->message ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                            No backup runs have been recorded yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</x-admin::layouts>
