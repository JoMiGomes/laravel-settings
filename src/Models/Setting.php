<?php

namespace YellowParadox\LaravelSettings\Models;

use YellowParadox\LaravelSettings\Casts\SecureDynamicTypeCasting;
use YellowParadox\LaravelSettings\DataTransferObjects\SettingData;
use YellowParadox\LaravelSettings\Services\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;

class Setting extends Model
{
    public const TYPE_INTEGER = 'integer';

    public const TYPE_DOUBLE = 'double';

    public const TYPE_BOOLEAN = 'boolean';

    public const TYPE_STRING = 'string';

    public const TYPE_ARRAY = 'array';

    public const TYPE_COLLECTION = 'collection';

    public const TYPE_OBJECT = 'object';

    public const TYPE_DATETIME = 'datetime';

    /**
     * The filename for the settings manifesto.
     *
     * @var string
     */
    public const SETTINGS_MANIFESTO_FILE_NAME = 'settings';

    /**
     * The class used for dynamic type casting of the 'value' attribute.
     */
    protected $casts = [
        'value' => SecureDynamicTypeCasting::class,
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'scope',
        'setting',
        'type',
        'value',
    ];

    /**
     * Gets a single setting, either default from manifesto or non-default from the database.
     * Returns a SettingData object for consistent object-like usage.
     *
     * @return SettingData|null
     */
    public static function get(string $setting, Model|string $scope): ?SettingData
    {
        return self::getService()->get($setting, $scope);
    }


    /**
     * Sets a single setting when non-default value is provided.
     * Also deletes the non-default (database record) value, if a value is returned back to default.
     */
    public static function set(string $setting, mixed $newValue, Model|string $scope): SettingData
    {
        return self::getService()->set($setting, $newValue, $scope);
    }


    /**
     * Gets a collection of all settings for a given scope.
     */
    public static function getAllScoped(Model|string $scope): Collection
    {
        return self::getService()->getAllScoped($scope);
    }


    /**
     * Gets a collection of settings based on a provided filter.
     * Dot notation is required to access deeper levels of grouped settings.
     */
    public static function getFiltered(Model|string $scope, string $filter): Collection
    {
        return self::getService()->getFiltered($scope, $filter);
    }


    /**
     * Get the parent settingable model.
     */
    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the settings service instance.
     */
    private static function getService(): SettingsService
    {
        return app(SettingsService::class);
    }
}
