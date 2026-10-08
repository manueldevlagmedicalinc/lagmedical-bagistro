<?php

namespace LagMedical\Eyewear\Providers;

use Illuminate\Support\ServiceProvider;

class EyewearServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'eyewear');
    }
}
