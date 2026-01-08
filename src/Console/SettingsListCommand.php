<?php

namespace YellowParadox\LaravelSettings\Console;

use YellowParadox\LaravelSettings\Models\Setting;
use Illuminate\Console\Command;

class SettingsListCommand extends Command
{
    protected $signature = 'settings:list 
                            {scope? : The scope to list settings for}
                            {--filter= : Filter settings by group (dot notation)}
                            {--only-custom : Show only customized (non-default) settings}';

    protected $description = 'List all settings or settings for a specific scope';

    public function handle(): int
    {
        $scope = $this->argument('scope');
        $filter = $this->option('filter');
        $onlyCustom = $this->option('only-custom');

        if (!$scope) {
            $this->listAllScopes();
            return self::SUCCESS;
        }

        try {
            $settings = $filter 
                ? Setting::getFiltered($scope, $filter)
                : Setting::getAllScoped($scope);

            if ($onlyCustom) {
                $settings = $settings->filter(fn($setting) => !$setting->isDefault);
            }

            if ($settings->isEmpty()) {
                $this->info('No settings found.');
                return self::SUCCESS;
            }

            $this->displaySettings($settings, $scope);
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function listAllScopes(): void
    {
        $config = config(Setting::SETTINGS_MANIFESTO_FILE_NAME, []);
        
        if (empty($config)) {
            $this->info('No scopes configured.');
            return;
        }

        $this->info('Available scopes:');
        $this->newLine();

        foreach (array_keys($config) as $scope) {
            $settingsCount = $this->countSettings($config[$scope]);
            $customCount = Setting::where('scope', $scope)->count();
            
            $this->line("  <fg=cyan>{$scope}</> ({$settingsCount} total, {$customCount} customized)");
        }

        $this->newLine();
        $this->comment('Run "settings:list {scope}" to view settings for a specific scope.');
    }

    private function countSettings(array $config, int $count = 0): int
    {
        foreach ($config as $key => $value) {
            if (is_array($value) && isset($value['type'], $value['value'])) {
                $count++;
            } elseif (is_array($value)) {
                $count = $this->countSettings($value, $count);
            }
        }
        
        return $count;
    }

    private function displaySettings($settings, string $scope): void
    {
        $this->info("Settings for scope: <fg=cyan>{$scope}</>");
        $this->newLine();

        $rows = $settings->map(function ($setting) {
            $value = $this->formatValue($setting->value);
            $status = $setting->isDefault ? '<fg=gray>default</>' : '<fg=green>custom</>';
            
            return [
                $setting->setting,
                $setting->type,
                $value,
                $status,
            ];
        })->toArray();

        $this->table(
            ['Setting', 'Type', 'Value', 'Status'],
            $rows
        );
    }

    private function formatValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return json_encode($value->toArray());
        }

        if ($value instanceof \Carbon\Carbon) {
            return $value->toDateTimeString();
        }

        if (is_object($value)) {
            return json_encode($value);
        }

        if (is_string($value) && strlen($value) > 50) {
            return substr($value, 0, 47) . '...';
        }

        return (string) $value;
    }
}
