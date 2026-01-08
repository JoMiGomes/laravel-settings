<?php

namespace YellowParadox\LaravelSettings\Events;

use YellowParadox\LaravelSettings\DataTransferObjects\SettingData;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly SettingData $setting,
        public readonly mixed $previousValue
    ) {
    }
}
