<?php

namespace YellowParadox\LaravelSettings\Models;

use YellowParadox\LaravelSettings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class BaseSetting extends Model
{
    private const EXCEPTION_MSG_TYPE_NOT_FOUND = "Type not found for setting '%s'.";

    private const EXCEPTION_MSG_DEFAULT_VALUE_NOT_FOUND = "Default value not found for setting '%s'.";

    private const EXCEPTION_MSG_DECLARED_TYPE_MISMATCH = "Declared type '%s' for setting '%s' does not match the actual %s.";

    private const EXCEPTION_MSG_SETTING_NOT_FOUND = "Setting '%s' not found in the config for scope '%s'.";

    private const EXCEPTION_MSG_MODEL_MISSING_TRAIT = 'The model must use the HasSettings trait.';

    /**
     * Validates parameters when interacting with settings.
     */
    protected static function validateParams(string $setting, Model|string $scope, mixed $newValue = null): array
    {
        $scopeString = self::validateScope($scope);

        return self::validateSetting($setting, $scopeString, $newValue);
    }

    /**
     * Validates the scope when interacting with settings.
     */
    protected static function validateScope(Model|string $scope): string
    {
        if ($scope instanceof Model && ! in_array(HasSettings::class, class_uses($scope), true)) {
            throw new InvalidArgumentException(self::EXCEPTION_MSG_MODEL_MISSING_TRAIT);
        }

        $scopeString = $scope instanceof Model ? $scope->getSettingsScope() : $scope;

        if (! config(sprintf('%s.%s', Setting::SETTINGS_MANIFESTO_FILE_NAME, $scopeString))) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_SETTING_NOT_FOUND, 'Scope', $scopeString));
        }

        return $scopeString;
    }

    /**
     * Validates the requested setting.
     */
    protected static function validateSetting(string $setting, string $scopeString, mixed $newValue = null): array
    {
        $settingKey = self::getSettingKey($setting, $scopeString);

        self::validateType($setting, $settingKey, $newValue);

        return [$scopeString, $settingKey, $settingKey['value']];
    }

    /**
     * Retrieves a manifesto setting by checking its existence in the manifesto.
     */
    private static function getSettingKey(string $setting, string $scopeString): array
    {
        $settingKey = config(sprintf('%s.%s.%s', Setting::SETTINGS_MANIFESTO_FILE_NAME, $scopeString, $setting));

        if (empty($settingKey)) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_SETTING_NOT_FOUND, $setting, $scopeString));
        }

        return $settingKey;
    }

    /**
     * Validates type declaration in manifesto and type matching.
     */
    protected static function validateType(string $setting, array $settingKey, mixed $newValue = null): void
    {
        if (! array_key_exists('type', $settingKey)) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_TYPE_NOT_FOUND, $setting));
        }

        $typeFromConfig = $settingKey['type'];

        if (! isset($settingKey['value'])) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_DEFAULT_VALUE_NOT_FOUND, $setting));
        }

        $defaultValue = $settingKey['value'];

        $defaultActualType = self::determineDefaultActualType($typeFromConfig, $defaultValue);

        if ($typeFromConfig !== $defaultActualType) {
            $errorMessage = sprintf(
                self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH,
                $typeFromConfig,
                $setting,
                "default value type ('$defaultActualType')"
            );

            throw new InvalidArgumentException($errorMessage);
        }

        if ($newValue !== null) {
            self::validateNewValueType($setting, $typeFromConfig, $newValue);
        }
    }

    /**
     * Determines the manifesto declared type of a setting and checks if value type is a match.
     */
    private static function determineDefaultActualType(string $typeFromConfig, mixed $defaultValue): string
    {
        if ($typeFromConfig === Setting::TYPE_DATETIME) {
            return (is_string($defaultValue) && strtotime($defaultValue) !== false) || $defaultValue instanceof Carbon
                ? Setting::TYPE_DATETIME
                : get_class($defaultValue);
        }

        return $typeFromConfig === Setting::TYPE_COLLECTION && $defaultValue instanceof Collection
            ? Setting::TYPE_COLLECTION
            : gettype($defaultValue);
    }

    /**
     * Validates type match for a new setting value against manifesto type declaration.
     */
    private static function validateNewValueType(string $setting, string $typeFromConfig, mixed $newValue): void
    {
        if ($typeFromConfig === Setting::TYPE_OBJECT && ! is_object($newValue)) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, 'object', $setting, 'object value'));
        }

        if ($typeFromConfig === Setting::TYPE_DATETIME && ! $newValue instanceof Carbon) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, Carbon::class, $setting, 'Carbon instance value'));
        }

        if ($typeFromConfig === Setting::TYPE_COLLECTION && ! $newValue instanceof Collection) {
            throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, Collection::class, $setting, 'Collection instance value'));
        }

        if ($typeFromConfig !== Setting::TYPE_DATETIME && $typeFromConfig !== Setting::TYPE_COLLECTION && $typeFromConfig !== Setting::TYPE_OBJECT) {
            $newValueActualType = gettype($newValue);

            if ($typeFromConfig !== $newValueActualType && ! ($typeFromConfig === Setting::TYPE_DATETIME && is_string($newValue) && strtotime($newValue) !== false)) {
                throw new InvalidArgumentException(sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, $typeFromConfig, $setting, "new value type ('$newValueActualType')"));
            }
        }
    }
}
