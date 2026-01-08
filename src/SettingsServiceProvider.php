<?php

namespace YellowParadox\LaravelSettings;

use YellowParadox\LaravelSettings\Contracts\SettingValidatorInterface;
use YellowParadox\LaravelSettings\Contracts\SettingsRepositoryInterface;
use YellowParadox\LaravelSettings\Repositories\SettingsRepository;
use YellowParadox\LaravelSettings\Services\SettingsService;
use YellowParadox\LaravelSettings\Validators\SettingValidator;
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
            __DIR__.'/../database/migrations/2024_01_01_000000_create_settings_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_settings_table.php'),
        ], 'settings-migrations');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }
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

        $this->app->singleton(SettingValidatorInterface::class, SettingValidator::class);
        $this->app->singleton(SettingsRepositoryInterface::class, SettingsRepository::class);
        $this->app->singleton(SettingsService::class);
        
        $this->app->alias(SettingsService::class, 'settings');
    }
    
    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            SettingValidatorInterface::class,
            SettingsRepositoryInterface::class,
            SettingsService::class,
            'settings',
        ];
    }
}
