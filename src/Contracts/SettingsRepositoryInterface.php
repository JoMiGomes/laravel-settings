<?php

namespace YellowParadox\LaravelSettings\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use YellowParadox\LaravelSettings\DataTransferObjects\SettingData;
use YellowParadox\LaravelSettings\Models\Setting;

interface SettingsRepositoryInterface
{
    public function find(string $setting, Model|string $scope): ?Setting;

    public function create(string $setting, mixed $value, string $type, Model|string $scope, string $scopeString): Setting;

    public function update(Setting $setting, mixed $value): Setting;

    public function delete(string $setting, Model|string $scope): void;

    public function getAllForScope(Model|string $scope): Collection;

    public function getFilteredForScope(Model|string $scope, string $filter): Collection;

    public function getDefaultFromConfig(string $setting, string $scope): ?array;

    public function getAllDefaultsFromConfig(string $scope): Collection;

    public function getFilteredDefaultsFromConfig(string $scope, string $filter): Collection;
}
