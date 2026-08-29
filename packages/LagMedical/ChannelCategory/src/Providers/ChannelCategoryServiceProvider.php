<?php

namespace LagMedical\ChannelCategory\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use LagMedical\ChannelCategory\DataGrids\Admin\CategoryDataGrid as ChannelAwareCategoryDataGrid;
use LagMedical\ChannelCategory\Http\Controllers\Shop\API\CategoryController as ChannelAwareApiCategoryController;
use LagMedical\ChannelCategory\Http\Controllers\Shop\HomeController as ChannelAwareHomeController;
use LagMedical\ChannelCategory\Http\Controllers\Shop\ProductsCategoriesProxyController as ChannelAwareProductsCategoriesProxyController;
use LagMedical\ChannelCategory\Http\Middleware\UseChannelAssetHost;
use LagMedical\ChannelCategory\Services\CategoryChannelService;
use Webkul\Admin\DataGrids\Catalog\CategoryDataGrid as BaseCategoryDataGrid;
use Webkul\Shop\Http\Controllers\API\CategoryController as BaseApiCategoryController;
use Webkul\Shop\Http\Controllers\HomeController as BaseHomeController;
use Webkul\Shop\Http\Controllers\ProductsCategoriesProxyController as BaseProductsCategoriesProxyController;
use Webkul\Theme\ViewRenderEventManager;

class ChannelCategoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BaseCategoryDataGrid::class, ChannelAwareCategoryDataGrid::class);
        $this->app->bind(BaseApiCategoryController::class, ChannelAwareApiCategoryController::class);
        $this->app->bind(BaseHomeController::class, ChannelAwareHomeController::class);
        $this->app->bind(BaseProductsCategoriesProxyController::class, ChannelAwareProductsCategoriesProxyController::class);
    }

    public function boot(CategoryChannelService $categoryChannelService): void
    {
        $this->app['router']->prependMiddlewareToGroup('web', UseChannelAssetHost::class);

        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'lagmedical-channel-category');
        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/components', 'shop');
        Blade::component(
            'lagmedical-channel-category::components.layouts.header.desktop.bottom',
            'shop::layouts.header.desktop.bottom'
        );
        Blade::component(
            'lagmedical-channel-category::components.layouts.header.mobile.index',
            'shop::layouts.header.mobile'
        );

        Event::listen('bagisto.admin.catalog.categories.create.card.accordion.settings.after', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('lagmedical-channel-category::admin.catalog.categories.channel-selector');
        });

        Event::listen('bagisto.admin.catalog.categories.edit.card.accordion.settings.after', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('lagmedical-channel-category::admin.catalog.categories.channel-selector');
        });

        Event::listen('bagisto.shop.layout.header.before', static function (ViewRenderEventManager $manager): void {
            $manager->addTemplate('lagmedical-channel-category::shop.header.clear-category-cache');
        });

        Event::listen('catalog.category.create.after', static function ($category) use ($categoryChannelService): void {
            $categoryChannelService->sync($category->id, request()->input('channel_ids', []));
        });

        Event::listen('catalog.category.update.after', static function ($category) use ($categoryChannelService): void {
            $categoryChannelService->sync($category->id, request()->input('channel_ids', []));
        });
    }
}
