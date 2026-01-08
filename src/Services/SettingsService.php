<?php

namespace YellowParadox\LaravelSettings\Services;

use YellowParadox\LaravelSettings\Contracts\SettingValidatorInterface;
use YellowParadox\LaravelSettings\Contracts\SettingsRepositoryInterface;
use YellowParadox\LaravelSettings\DataTransferObjects\SettingData;
use YellowParadox\LaravelSettings\Events\SettingCreated;
use YellowParadox\LaravelSettings\Events\SettingDeleted;
use YellowParadox\LaravelSettings\Events\SettingRetrieved;
use YellowParadox\LaravelSettings\Events\SettingUpdated;
use YellowParadox\LaravelSettings\Models\Setting;
use YellowParadox\LaravelSettings\Traits\HasSettings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class SettingsService
{
    public function __construct(
        private readonly SettingsRepositoryInterface $repository,
        private readonly SettingValidatorInterface $validator
    ) {
    }

    public function get(string $setting, Model|string $scope): SettingData|null
    {
        [$scopeString, $settingKey] = $this->validateAndPrepare($setting, $scope);

        $settingModel = $this->repository->find($setting, $scope);

        if ($settingModel) {
            if ($settingModel->type === Setting::TYPE_DATETIME) {
                $settingModel->value = Carbon::parse($settingModel->value);
            }

            $settingData = SettingData::fromModel($settingModel);
            event(new SettingRetrieved($settingData));
            
            return $settingData;
        }

        $defaultValue = $this->repository->getDefaultFromConfig($setting, $scopeString);

        if ($defaultValue !== null) {
            $settingData = SettingData::fromConfig(
                $setting,
                $settingKey['type'],
                $defaultValue['value'],
                $scopeString
            );
            
            event(new SettingRetrieved($settingData));
            
            return $settingData;
        }

        return null;
    }

    public function set(string $setting, mixed $newValue, Model|string $scope): SettingData
    {
        [$scopeString, $settingKey, $defaultValue] = $this->validator->validate($setting, $this->getScopeString($scope), $newValue);

        $existingSetting = $this->repository->find($setting, $scope);
        $previousValue = $existingSetting ? $existingSetting->value : $defaultValue;

        if ($newValue === $defaultValue) {
            if ($existingSetting) {
                $this->repository->delete($setting, $scope);
                event(new SettingDeleted($setting, $scopeString, $previousValue));
            }

            return SettingData::fromConfig(
                $setting,
                $settingKey['type'],
                $defaultValue,
                $scopeString
            );
        }

        $settingModel = $this->repository->create(
            $setting,
            $newValue,
            $settingKey['type'],
            $scope,
            $scopeString
        );

        $settingData = SettingData::fromModel($settingModel);
        
        if ($existingSetting) {
            event(new SettingUpdated($settingData, $previousValue));
        } else {
            event(new SettingCreated($settingData, $previousValue));
        }

        return $settingData;
    }

    public function getAllScoped(Model|string $scope): Collection
    {
        $scopeString = $this->getScopeString($scope);
        $this->validator->validateScope($scopeString);

        $nonDefaultSettings = $this->repository->getAllForScope($scope);
        $defaultSettingPaths = $this->repository->getAllDefaultsFromConfig($scopeString);

        if ($nonDefaultSettings->isNotEmpty()) {
            $defaultSettingPaths = $defaultSettingPaths->reject(function ($settingPath) use ($nonDefaultSettings) {
                return $nonDefaultSettings->contains('setting', $settingPath);
            });
        }

        $defaultSettingsData = $defaultSettingPaths->map(function ($settingPath) use ($scopeString) {
            $config = $this->repository->getDefaultFromConfig($settingPath, $scopeString);
            
            return SettingData::fromConfig(
                $settingPath,
                $config['type'],
                $config['value'],
                $scopeString
            );
        });

        $nonDefaultSettingsData = $nonDefaultSettings->map(fn($setting) => SettingData::fromModel($setting));

        return collect($nonDefaultSettingsData)->merge($defaultSettingsData);
    }

    public function getFiltered(Model|string $scope, string $filter): Collection
    {
        $scopeString = $this->getScopeString($scope);
        $this->validator->validateScope($scopeString);

        $nonDefaultSettingsFiltered = $this->repository->getFilteredForScope($scope, $filter);
        $defaultSettingPaths = $this->repository->getFilteredDefaultsFromConfig($scopeString, $filter);

        if ($nonDefaultSettingsFiltered->isNotEmpty()) {
            $defaultSettingPaths = $defaultSettingPaths->reject(function ($settingPath) use ($nonDefaultSettingsFiltered) {
                return $nonDefaultSettingsFiltered->contains('setting', $settingPath);
            });
        }

        $defaultSettingsData = $defaultSettingPaths->map(function ($settingPath) use ($scopeString) {
            $config = $this->repository->getDefaultFromConfig($settingPath, $scopeString);
            
            return SettingData::fromConfig(
                $settingPath,
                $config['type'],
                $config['value'],
                $scopeString
            );
        });

        $nonDefaultSettingsData = $nonDefaultSettingsFiltered->map(fn($setting) => SettingData::fromModel($setting));

        return collect($nonDefaultSettingsData)->merge($defaultSettingsData);
    }

    private function validateAndPrepare(string $setting, Model|string $scope): array
    {
        $scopeString = $this->getScopeString($scope);
        
        return $this->validator->validate($setting, $scopeString);
    }

    private function getScopeString(Model|string $scope): string
    {
        if ($scope instanceof Model) {
            if (!in_array(HasSettings::class, class_uses($scope), true)) {
                throw new InvalidArgumentException('The model must use the HasSettings trait.');
            }

            return $scope->getSettingsScope();
        }

        return $scope;
    }
}
