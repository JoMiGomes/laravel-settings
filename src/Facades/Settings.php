<?php

namespace YellowParadox\LaravelSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \YellowParadox\LaravelSettings\DataTransferObjects\SettingData|null get(string $setting, \Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \YellowParadox\LaravelSettings\DataTransferObjects\SettingData set(string $setting, mixed $newValue, \Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \Illuminate\Support\Collection getAllScoped(\Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \Illuminate\Support\Collection getFiltered(\Illuminate\Database\Eloquent\Model|string $scope, string $filter)
 *
 * @see \YellowParadox\LaravelSettings\Services\SettingsService
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \YellowParadox\LaravelSettings\Services\SettingsService::class;
    }
}
