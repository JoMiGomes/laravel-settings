<?php

namespace YellowParadox\LaravelSettings\Repositories;

use YellowParadox\LaravelSettings\Contracts\SettingsRepositoryInterface;
use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SettingsRepository implements SettingsRepositoryInterface
{
    public function find(string $setting, Model|string $scope): ?Setting
    {
        return $scope instanceof Model
            ? $scope->settings()->where('setting', $setting)->first()
            : Setting::where('setting', $setting)->where('scope', $scope)->first();
    }

    public function create(string $setting, mixed $value, string $type, Model|string $scope, string $scopeString): Setting
    {
        $builder = $scope instanceof Model
            ? $scope->settings()
            : Setting::query()->where('setting', $setting)->where('scope', $scopeString);

        return $scope instanceof Model
            ? $builder->updateOrCreate(
                ['setting' => $setting],
                ['type' => $type, 'value' => $value]
            )
            : $builder->updateOrCreate(
                ['setting' => $setting],
                ['type' => $type, 'value' => $value, 'scope' => $scopeString]
            );
    }

    public function update(Setting $setting, mixed $value): Setting
    {
        $setting->value = $value;
        $setting->save();

        return $setting->fresh();
    }

    public function delete(string $setting, Model|string $scope): void
    {
        $existingValue = $this->find($setting, $scope);

        if ($existingValue) {
            $existingValue->delete();
        }
    }

    public function getAllForScope(Model|string $scope): Collection
    {
        return $scope instanceof Model
            ? $scope->settings
            : Setting::where('scope', $scope)->get();
    }

    public function getFilteredForScope(Model|string $scope, string $filter): Collection
    {
        return $scope instanceof Model
            ? $scope->settings()->where('setting', 'like', "$filter%")->get()
            : Setting::where('scope', $scope)->where('setting', 'like', "$filter%")->get();
    }

    public function getDefaultFromConfig(string $setting, string $scope): ?array
    {
        return config(sprintf('%s.%s.%s', Setting::SETTINGS_MANIFESTO_FILE_NAME, $scope, $setting));
    }

    public function getAllDefaultsFromConfig(string $scope): Collection
    {
        $settingsManifesto = config(Setting::SETTINGS_MANIFESTO_FILE_NAME . '.' . $scope, []);
        
        return $this->collectSettingsPaths($settingsManifesto);
    }

    public function getFilteredDefaultsFromConfig(string $scope, string $filter): Collection
    {
        $settingsManifesto = config(Setting::SETTINGS_MANIFESTO_FILE_NAME . '.' . $scope, []);
        $filteredSettings = Arr::get($settingsManifesto, $filter, []);

        return $this->processFilteredSettings($filteredSettings, $filter);
    }

    private function collectSettingsPaths(array $settings, string $parentSetting = ''): Collection
    {
        $result = collect();

        foreach ($settings as $setting => $value) {
            $settingPath = $parentSetting ? "$parentSetting.$setting" : $setting;

            if (is_array($value) && !isset($value['value'], $value['type'])) {
                $nestedSettings = $this->collectSettingsPaths($value, $settingPath);
                $result = $result->merge($nestedSettings);
            } else {
                $result->add($settingPath);
            }
        }

        return $result;
    }

    private function processFilteredSettings(array $filteredSettings, string $filter, string $parentSetting = ''): Collection
    {
        $result = collect();

        foreach ($filteredSettings as $setting => $value) {
            $settingPath = $parentSetting ? "$parentSetting.$setting" : $setting;

            if (is_array($value) && !isset($value['value'], $value['type'])) {
                $nestedSettings = $this->processFilteredSettings($value, $filter, $settingPath);
                $result = $result->merge($nestedSettings);
            } else {
                $result->add(sprintf('%s.%s', $filter, $settingPath));
            }
        }

        return $result;
    }
}
