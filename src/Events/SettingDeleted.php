<?php

namespace YellowParadox\LaravelSettings\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $setting,
        public readonly string $scope,
        public readonly mixed $previousValue
    ) {
    }
}
