<?php

namespace LagMedical\CmsPro\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;
use LagMedical\CmsPro\Http\Controllers\Admin\CmsPageController;
use LagMedical\CmsPro\Services\CmsProService;
use LagMedical\CmsPro\Services\ProductBlockService;
use Webkul\Admin\Http\Controllers\CMS\PageController as NativeCmsPageController;
use Webkul\Theme\ViewRenderEventManager;

class CmsProServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../Config/acl.php', 'acl');
        $this->app->scoped(CmsProService::class);
        $this->app->singleton(ProductBlockService::class);
        $this->app->bind(NativeCmsPageController::class, CmsPageController::class);
    }

    public function boot(CmsProService $cmsPro): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'cms-pro');

        Route::middleware('web')->group(__DIR__.'/../Routes/admin-routes.php');

        Event::listen('bagisto.admin.cms.pages.create.card.description.before', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('cms-pro::admin.cms.type');
        });

        Event::listen('bagisto.admin.cms.pages.edit.card.content.before', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('cms-pro::admin.cms.type');
        });

        Event::listen('bagisto.admin.cms.pages.edit.card.content.after', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('cms-pro::admin.cms.entry');
        });

        Event::listen('bagisto.admin.cms.pages.list.after', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('cms-pro::admin.cms.action');
        });

        Event::listen('cms.page.create.after', static function ($page) use ($cmsPro): void {
            $cmsPro->setPageType($page->id, request()->input('cms_pro_type', 'native'));
        });

        Event::listen('cms.page.update.after', static function ($page) use ($cmsPro): void {
            if (request()->has('cms_pro_type')) {
                $cmsPro->setPageType($page->id, request()->input('cms_pro_type'));
            }
        });

        Event::listen('cms.page.delete.before', static function ($pageId): void {
            if (Schema::hasTable('cms_pro_page_settings')) {
                DB::table('cms_pro_page_settings')->where('cms_page_id', $pageId)->delete();
            }
        });

        $this->app->booted(function (): void {
            view()->prependNamespace('shop', __DIR__.'/../Resources/views/shop-overrides');

            view()->composer('*', static function (View $view): void {
                $view->with('cmsProService', app(CmsProService::class));
            });
        });
    }
}
