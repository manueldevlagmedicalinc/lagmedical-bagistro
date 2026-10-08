<?php

namespace LagMedical\Eblast\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use LagMedical\Eblast\Services\EblastManager;

class SyncEblastContact implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        protected string $email,
        protected int $channelId,
        protected string $listKey
    ) {}

    public function handle(EblastManager $manager): void
    {
        $manager->sync($this->email, $this->channelId, $this->listKey);
    }
}
