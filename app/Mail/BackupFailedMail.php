<?php

namespace App\Mail;

use App\Models\BackupLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BackupFailedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BackupLog $backupLog) {}

    public function build(): self
    {
        return $this
            ->subject('Lag Medical backup failed')
            ->view('emails.backups.failed');
    }
}
