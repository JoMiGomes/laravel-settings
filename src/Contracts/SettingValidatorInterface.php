<?php

namespace YellowParadox\LaravelSettings\Contracts;

interface SettingValidatorInterface
{
    public function validate(string $setting, string $scope, mixed $newValue = null): array;

    public function validateScope(string $scope): void;

    public function validateType(string $type, mixed $value): void;
}
