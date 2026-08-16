<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    protected $table = 'lagmedical_backup_logs';

    protected $fillable = [
        'status',
        'monthly_snapshot',
        'started_at',
        'completed_at',
        'size_bytes',
        'files',
        'remote_path',
        'remote_url',
        'message',
    ];

    protected $casts = [
        'monthly_snapshot' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'files' => 'array',
    ];
}
