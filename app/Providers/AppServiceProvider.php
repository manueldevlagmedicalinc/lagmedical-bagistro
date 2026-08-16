<?php

namespace App\Providers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;
use Webkul\Theme\ViewRenderEventManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/lagmedical_helpers.php');

        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips', '')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerLagmedicalAdminMenu();
        $this->registerLagmedicalDashboardCards();

        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });
    }

    private function registerLagmedicalAdminMenu(): void
    {
        config()->set('menu.admin', array_merge(config('menu.admin', []), [
            [
                'key' => 'settings.backups',
                'name' => 'Backups',
                'route' => 'admin.settings.backups.index',
                'sort' => 11,
                'icon' => '',
            ],
        ]));

        config()->set('acl', array_merge(config('acl', []), [
            [
                'key' => 'settings.backups',
                'name' => 'Backups',
                'route' => 'admin.settings.backups.index',
                'sort' => 11,
            ],
        ]));
    }

    private function registerLagmedicalDashboardCards(): void
    {
        Event::listen('bagisto.admin.dashboard.stock_threshold.after', static function (ViewRenderEventManager $viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('admin.backups.dashboard-summary');
        });
    }
}
