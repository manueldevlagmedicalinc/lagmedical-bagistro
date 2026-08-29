<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\RunLagmedicalBackup;
use App\Models\BackupLog;
use Illuminate\Http\Request;

class BackupLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = BackupLog::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('remote_path', 'like', "%{$search}%")
                        ->orWhere('remote_url', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('files', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('started_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('started_at', '<=', $request->date('date_to')))
            ->latest('started_at')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.backups.index', compact('logs'));
    }

    public function run()
    {
        if (BackupLog::query()->whereIn('status', ['queued', 'running'])->exists()) {
            return back()->with('error', 'A backup is already queued or running.');
        }

        $backupLog = BackupLog::create([
            'status' => 'queued',
            'started_at' => now(),
            'message' => 'Backup queued from admin.',
        ]);

        RunLagmedicalBackup::dispatch($backupLog->id)
            ->onConnection('database')
            ->onQueue('backups');

        return back()->with('success', 'Backup queued successfully. Make sure the queue worker is running.');
    }
}
