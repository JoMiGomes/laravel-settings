<?php

namespace YellowParadox\LaravelSettings\Traits;

use YellowParadox\LaravelSettings\DataTransferObjects\SettingData;
use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Trait HasSettings
 *
 * This trait will enable settings that have to be related to a model.
 *
 * Settings will always have to be defined in config/settings.php, which is the single source of truth for all the
 * available settings throughout the app.
 */
trait HasSettings
{
    /**
     * Defines the relationship between model and settings.
     *
     * Do not fetch settings through relationship as it will lead to incorrect results (no defaults will be fetched).
     */
    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'settingable');
    }

    /**
     * Gets the simplified snake_case name of the related model.
     */
    public function getSettingsScope(): string
    {
        return Str::snake(class_basename($this->getMorphClass()));
    }

    /**
     * Gets a single setting, scoped to the current model.
     */
    public function getSetting(string $setting): ?SettingData
    {
        return Setting::get($setting, $this);
    }

    /**
     * Sets a single setting, scoped to the current model.
     */
    public function setSetting(string $setting, mixed $newValue): SettingData
    {
        return Setting::set($setting, $newValue, $this);
    }

    /**
     * Gets a collection of all settings, scoped to the current model.
     */
    public function getAllSettings(): Collection
    {
        return Setting::getAllScoped($this);
    }

    /**
     * Gets a collection of filtered settings, scoped to the current model.
     * Dot notation is required to access deeper levels of grouped settings.
     */
    public function getFilteredSettings(string $filter): Collection
    {
        return Setting::getFiltered($this, $filter);
    }
}
