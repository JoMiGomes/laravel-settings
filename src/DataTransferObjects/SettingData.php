<?php

namespace YellowParadox\LaravelSettings\DataTransferObjects;

use YellowParadox\LaravelSettings\Models\Setting;

class SettingData
{
    public function __construct(
        public readonly string $setting,
        public readonly string $type,
        public readonly mixed $value,
        public readonly string $scope,
        public readonly bool $isDefault = true,
        public readonly ?int $id = null
    ) {
    }

    public static function fromModel(Setting $setting): self
    {
        return new self(
            setting: $setting->setting,
            type: $setting->type,
            value: $setting->value,
            scope: $setting->scope ?? '',
            isDefault: false,
            id: $setting->id
        );
    }

    public static function fromConfig(string $setting, string $type, mixed $value, string $scope): self
    {
        return new self(
            setting: $setting,
            type: $type,
            value: $value,
            scope: $scope,
            isDefault: true,
            id: null
        );
    }

    public function toArray(): array
    {
        return [
            'setting' => $this->setting,
            'type' => $this->type,
            'value' => $this->value,
            'scope' => $this->scope,
            'is_default' => $this->isDefault,
            'id' => $this->id,
        ];
    }
}
