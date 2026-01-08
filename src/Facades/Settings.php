<?php

namespace JomiGomes\LaravelSettings\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \JomiGomes\LaravelSettings\DataTransferObjects\SettingData|null get(string $setting, \Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \JomiGomes\LaravelSettings\DataTransferObjects\SettingData set(string $setting, mixed $newValue, \Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \Illuminate\Support\Collection getAllScoped(\Illuminate\Database\Eloquent\Model|string $scope)
 * @method static \Illuminate\Support\Collection getFiltered(\Illuminate\Database\Eloquent\Model|string $scope, string $filter)
 *
 * @see \JomiGomes\LaravelSettings\Services\SettingsService
 */
class Settings extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JomiGomes\LaravelSettings\Services\SettingsService::class;
    }
}
