<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class LagmedicalMailTest extends Command
{
    protected $signature = 'lagmedical:mail-test {email=manuel@lagmedicalinc.com : Recipient email address}';

    protected $description = 'Send a test email using the configured Bagisto/Laravel mailer.';

    public function handle(): int
    {
        $recipient = $this->argument('email');

        Mail::raw(
            "This is a Lag Medical mail test.\n\nMailer: ".config('mail.default')."\nSent at: ".now()->toDateTimeString(),
            function ($message) use ($recipient) {
                $message
                    ->to($recipient)
                    ->subject('Lag Medical mail test');
            }
        );

        $this->info("Test email sent to {$recipient} using mailer [".config('mail.default').'].');

        return self::SUCCESS;
    }
}
