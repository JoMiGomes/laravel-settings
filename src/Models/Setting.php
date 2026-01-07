<?php

namespace YellowParadox\LaravelSettings\Models;

use YellowParadox\LaravelSettings\Casts\DynamicTypeCasting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use stdClass;

class Setting extends BaseSetting
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
        'value' => DynamicTypeCasting::class,
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
     * Default settings are instances of stdClass to provide object-like usage.
     *
     * @return stdClass|static|null
     */
    public static function get(string $setting, Model|string $scope): self|stdClass|null
    {
        [$scopeString, $settingKey] = self::validateParams($setting, $scope);

        $settingModel = self::getSettingModel($setting, $scope);

        if ($settingModel) {
            if ($settingModel->type === self::TYPE_DATETIME) {
                $settingModel->value = Carbon::parse($settingModel->value);
            }

            return $settingModel;
        }

        $defaultValue = config(sprintf('%s.%s.%s', self::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $setting));

        if ($defaultValue !== null) {
            return self::createGenericObject($setting, $settingKey['type'], $defaultValue['value'], $scopeString);
        }

        return null;
    }

    /**
     * Gets a single non-default setting from the database.
     *
     * @return static|null
     */
    protected static function getSettingModel(string $setting, Model|string $scope): ?self
    {
        return $scope instanceof Model
            ? $scope->settings()->where('setting', $setting)->first()
            : self::where('setting', $setting)->where('scope', $scope)->first();
    }

    /**
     * Generates a basic object for default settings to provide object-like usage.
     */
    private static function createGenericObject(string $setting, string $type, $value, string $scope): stdClass
    {
        return (object) [
            'setting' => $setting,
            'type' => $type,
            'value' => $value,
            'scope' => $scope,
        ];
    }

    /**
     * Sets a single setting when non-default value is provided.
     * Also deletes the non-default (database record) value, if a value is returned back to default.
     */
    public static function set(string $setting, mixed $newValue, Model|string $scope): Setting|stdClass
    {
        [$scopeString, $settingKey, $defaultValue] = self::validateParams($setting, $scope, $newValue);

        if ($newValue === $defaultValue) {
            self::deleteExistingSetting($setting, $scope);

            return self::createGenericObject($setting, $settingKey['type'], $defaultValue, $scopeString);
        }

        return self::createOrUpdateSetting($setting, $newValue, $scope, $scopeString, $settingKey['type']);
    }

    /**
     * Deletes a setting from the database.
     */
    protected static function deleteExistingSetting(string $setting, Model|string $scope): void
    {
        $existingValue = self::getSettingModel($setting, $scope);

        if ($existingValue) {
            $existingValue->delete();
        }
    }

    /**
     * Creates or updates a setting record on the database.
     */
    protected static function createOrUpdateSetting(string $setting, $newValue, Model|string $scope, string $scopeString, string $settingType): Setting
    {
        $builder = $scope instanceof Model
            ? $scope->settings()
            : self::query()->where('setting', $setting)->where('scope', $scopeString);

        return $scope instanceof Model
            ? $builder->updateOrCreate(
                ['setting' => $setting],
                ['type' => $settingType, 'value' => $newValue])
            : $builder->updateOrCreate(
                ['setting' => $setting],
                ['type' => $settingType, 'value' => $newValue, 'scope' => $scopeString]
            );
    }

    /**
     * Gets a collection of all settings for a given scope.
     */
    public static function getAllScoped(Model|string $scope): Collection
    {
        $scopeString = self::validateScope($scope);

        $nonDefaultSettings = self::getNonDefaultSettings($scope);

        $settingsManifesto = config(self::SETTINGS_MANIFESTO_FILE_NAME.'.'.$scopeString, []);
        $defaultSettings = self::collectSettingsPaths($settingsManifesto, $scopeString);

        if ($nonDefaultSettings->isNotEmpty()) {
            $defaultSettings = $defaultSettings->reject(function ($setting) use ($nonDefaultSettings) {
                return $nonDefaultSettings->contains('setting', $setting);
            });
        }
        $defaultSettingsObjects = collect();
        foreach ($defaultSettings as $settingPath) {
            $settingModel = self::createGenericObject(
                $settingPath,
                config(sprintf('%s.%s.%s.%s', self::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $settingPath, 'type')),
                config(sprintf('%s.%s.%s.%s', self::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $settingPath, 'value')),
                $scopeString
            );

            $defaultSettingsObjects->add($settingModel);
        }

        return collect($nonDefaultSettings)->merge($defaultSettingsObjects);
    }

    /**
     * Gets non-default settings from the database.
     */
    private static function getNonDefaultSettings(Model|string $scope): Collection
    {
        return $scope instanceof Model
            ? $scope->settings
            : self::where('scope', $scope)->get();
    }

    /**
     * Resolves paths of settings for querying.
     */
    private static function collectSettingsPaths(array $settings, string $scope, string $parentSetting = ''): Collection
    {
        $result = collect();

        foreach ($settings as $setting => $value) {
            $settingPath = $parentSetting ? "$parentSetting.$setting" : $setting;

            if (is_array($value) && ! isset($value['value'], $value['type'])) {
                $nestedSettings = self::collectSettingsPaths($value, $scope, $settingPath);
                $result = $result->merge($nestedSettings);
            } else {
                $result->add($settingPath);
            }
        }

        return $result;
    }

    /**
     * Gets a collection of settings based on a provided filter.
     * Dot notation is required to access deeper levels of grouped settings.
     */
    public static function getFiltered(Model|string $scope, string $filter): Collection
    {
        $scopeString = self::validateScope($scope);

        $nonDefaultSettingsFiltered = self::getNonDefaultSettingsFiltered($scope, $filter);

        $settingsManifesto = config(self::SETTINGS_MANIFESTO_FILE_NAME.'.'.$scopeString, []);
        $filteredSettings = Arr::get($settingsManifesto, $filter, []);

        $defaultSettingsObjects = self::processFilteredSettings($filteredSettings, $scopeString, $filter);

        if ($nonDefaultSettingsFiltered->isNotEmpty()) {
            $defaultSettingsObjects = $defaultSettingsObjects->reject(function ($settingPath) use ($nonDefaultSettingsFiltered) {
                return $nonDefaultSettingsFiltered->contains('setting', $settingPath);
            });
        }

        if ($defaultSettingsObjects->isNotEmpty()) {
            $defaultSettingsObjects = $defaultSettingsObjects->map(function ($settingPath) use ($scopeString) {
                return self::createGenericObject(
                    $settingPath,
                    config(sprintf('%s.%s.%s.%s', self::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $settingPath, 'type')),
                    config(sprintf('%s.%s.%s.%s', self::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $settingPath, 'value')),
                    $scopeString
                );
            });
        }

        return collect($nonDefaultSettingsFiltered)->merge($defaultSettingsObjects);
    }

    /**
     * Gets a collection of filtered queried settings from the database.
     */
    private static function getNonDefaultSettingsFiltered(Model|string $scope, string $filter): Collection
    {
        return $scope instanceof Model
            ? $scope->settings()->where('setting', 'like', "$filter%")->get()
            : self::where('scope', $scope)->where('setting', 'like', "$filter%")->get();
    }

    /**
     * Processes settings paths for filtered queries.
     */
    private static function processFilteredSettings(array $filteredSettings, string $scopeString, string $filter, string $parentSetting = ''): Collection
    {
        $result = collect();

        foreach ($filteredSettings as $setting => $value) {
            $settingPath = $parentSetting ? "$parentSetting.$setting" : $setting;

            if (is_array($value) && ! isset($value['value'], $value['type'])) {
                $nestedSettings = self::processFilteredSettings($value, $scopeString, $filter, $settingPath);
                $result = $result->merge($nestedSettings);
            } else {
                $result->add(sprintf('%s.%s', $filter, $settingPath));
            }
        }

        return $result;
    }

    /**
     * Get the parent settingable model.
     */
    public function settingable(): MorphTo
    {
        return $this->morphTo();
    }
}
