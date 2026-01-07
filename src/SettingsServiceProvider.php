<?php

namespace YellowParadox\LaravelSettings;

use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/settings.php' => config_path('settings.php'),
        ], 'settings-config');

        $this->publishes([
            __DIR__.'/../database/migrations/create_settings_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_settings_table.php'),
        ], 'settings-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/settings.php',
            'settings'
        );
    }
}
