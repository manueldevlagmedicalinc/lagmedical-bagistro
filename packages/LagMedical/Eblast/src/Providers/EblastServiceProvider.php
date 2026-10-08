<?php

namespace LagMedical\Eblast\Providers;

use Illuminate\Support\ServiceProvider;
use LagMedical\Eblast\Services\EblastManager;

class EblastServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(EblastManager::class);

        config()->push('core', [
            'key' => 'emails.configure.eblast',
            'name' => 'Eblast / Brevo',
            'info' => 'Configure the Brevo eblast integration and campaign lists.',
            'sort' => 3,
            'fields' => [
                ['name' => 'enabled', 'title' => 'Enable Eblast', 'type' => 'boolean', 'channel_based' => true, 'default' => false],
                ['name' => 'api_key', 'title' => 'Brevo API key', 'type' => 'password', 'channel_based' => true],
                ['name' => 'folder_name', 'title' => 'Brevo folder name', 'type' => 'text', 'channel_based' => true, 'validation' => 'required', 'default' => 'landing-campign'],
                [
                    'name' => 'frames_campaign_list_key',
                    'title' => 'Frames campaign list ID',
                    'type' => 'number',
                    'channel_based' => true,
                    'validation' => 'required',
                    'default' => 9,
                    'info' => 'The Brevo list ID used by the /campaign/frames/ landing.',
                ],
                [
                    'name' => 'lists',
                    'title' => 'Brevo lists',
                    'type' => 'blade',
                    'path' => 'eblast::configuration.lists',
                    'channel_based' => true,
                    'validation' => 'required',
                    'default' => '[{"key":"frames-campaign-landing-list","name":"Landing-LagMedical-Campaing","id":9}]',
                ],
            ],
        ]);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/admin-routes.php');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'eblast');
    }
}
