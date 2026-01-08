<?php

namespace YellowParadox\LaravelSettings\Validators;

use YellowParadox\LaravelSettings\Contracts\SettingValidatorInterface;
use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SettingValidator implements SettingValidatorInterface
{
    private const EXCEPTION_MSG_TYPE_NOT_FOUND = "Type not found for setting '%s'.";
    private const EXCEPTION_MSG_DEFAULT_VALUE_NOT_FOUND = "Default value not found for setting '%s'.";
    private const EXCEPTION_MSG_DECLARED_TYPE_MISMATCH = "Declared type '%s' for setting '%s' does not match the actual %s.";
    private const EXCEPTION_MSG_SETTING_NOT_FOUND = "Setting '%s' not found in the config for scope '%s'.";

    public function validate(string $setting, string $scope, mixed $newValue = null): array
    {
        $this->validateScope($scope);
        
        $settingKey = $this->getSettingKey($setting, $scope);
        
        $this->validateSettingStructure($setting, $settingKey, $newValue);
        
        return [$scope, $settingKey, $settingKey['value']];
    }

    public function validateScope(string $scope): void
    {
        if (!config(sprintf('%s.%s', Setting::SETTINGS_MANIFESTO_FILE_NAME, $scope))) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_SETTING_NOT_FOUND, 'Scope', $scope)
            );
        }
    }

    public function validateType(string $type, mixed $value): void
    {
        $isValid = match ($type) {
            Setting::TYPE_INTEGER => is_int($value),
            Setting::TYPE_DOUBLE => is_float($value),
            Setting::TYPE_BOOLEAN => is_bool($value),
            Setting::TYPE_STRING => is_string($value),
            Setting::TYPE_ARRAY => is_array($value),
            Setting::TYPE_COLLECTION => $value instanceof Collection,
            Setting::TYPE_OBJECT => is_object($value),
            Setting::TYPE_DATETIME => $value instanceof Carbon,
            default => false,
        };

        if (!$isValid) {
            throw new InvalidArgumentException(
                sprintf("Value type does not match expected type '%s'", $type)
            );
        }
    }

    private function getSettingKey(string $setting, string $scope): array
    {
        $settingKey = config(
            sprintf('%s.%s.%s', Setting::SETTINGS_MANIFESTO_FILE_NAME, $scope, $setting)
        );

        if (empty($settingKey)) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_SETTING_NOT_FOUND, $setting, $scope)
            );
        }

        return $settingKey;
    }

    private function validateSettingStructure(string $setting, array $settingKey, mixed $newValue = null): void
    {
        if (!array_key_exists('type', $settingKey)) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_TYPE_NOT_FOUND, $setting)
            );
        }

        $typeFromConfig = $settingKey['type'];

        if (!isset($settingKey['value'])) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_DEFAULT_VALUE_NOT_FOUND, $setting)
            );
        }

        $defaultValue = $settingKey['value'];

        $this->validateDefaultValueType($setting, $typeFromConfig, $defaultValue);

        if ($newValue !== null) {
            $this->validateNewValueType($setting, $typeFromConfig, $newValue);
        }
    }

    private function validateDefaultValueType(string $setting, string $typeFromConfig, mixed $defaultValue): void
    {
        $defaultActualType = $this->determineActualType($typeFromConfig, $defaultValue);

        if ($typeFromConfig !== $defaultActualType) {
            $errorMessage = sprintf(
                self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH,
                $typeFromConfig,
                $setting,
                "default value type ('$defaultActualType')"
            );

            throw new InvalidArgumentException($errorMessage);
        }
    }

    private function determineActualType(string $typeFromConfig, mixed $value): string
    {
        if ($typeFromConfig === Setting::TYPE_DATETIME) {
            return (is_string($value) && strtotime($value) !== false) || $value instanceof Carbon
                ? Setting::TYPE_DATETIME
                : get_class($value);
        }

        return $typeFromConfig === Setting::TYPE_COLLECTION && $value instanceof Collection
            ? Setting::TYPE_COLLECTION
            : gettype($value);
    }

    private function validateNewValueType(string $setting, string $typeFromConfig, mixed $newValue): void
    {
        if ($typeFromConfig === Setting::TYPE_OBJECT && !is_object($newValue)) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, 'object', $setting, 'object value')
            );
        }

        if ($typeFromConfig === Setting::TYPE_DATETIME && !$newValue instanceof Carbon) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, Carbon::class, $setting, 'Carbon instance value')
            );
        }

        if ($typeFromConfig === Setting::TYPE_COLLECTION && !$newValue instanceof Collection) {
            throw new InvalidArgumentException(
                sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, Collection::class, $setting, 'Collection instance value')
            );
        }

        if (!in_array($typeFromConfig, [Setting::TYPE_DATETIME, Setting::TYPE_COLLECTION, Setting::TYPE_OBJECT], true)) {
            $newValueActualType = gettype($newValue);

            if ($typeFromConfig !== $newValueActualType && 
                !($typeFromConfig === Setting::TYPE_DATETIME && is_string($newValue) && strtotime($newValue) !== false)) {
                throw new InvalidArgumentException(
                    sprintf(self::EXCEPTION_MSG_DECLARED_TYPE_MISMATCH, $typeFromConfig, $setting, "new value type ('$newValueActualType')")
                );
            }
        }
    }
}
