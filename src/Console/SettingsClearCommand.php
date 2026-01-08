<?php

namespace JomiGomes\LaravelSettings\Console;

use JomiGomes\LaravelSettings\Models\Setting;
use Illuminate\Console\Command;

class SettingsClearCommand extends Command
{
    protected $signature = 'settings:clear 
                            {scope? : The scope to clear settings for}
                            {--filter= : Clear only settings matching this filter (dot notation)}
                            {--force : Force the operation without confirmation}';

    protected $description = 'Clear customized settings (revert to defaults)';

    public function handle(): int
    {
        $scope = $this->argument('scope');
        $filter = $this->option('filter');
        $force = $this->option('force');

        if (!$scope) {
            return $this->clearAllScopes($force);
        }

        try {
            $query = Setting::where('scope', $scope);

            if ($filter) {
                $query->where('setting', 'like', "{$filter}%");
            }

            $count = $query->count();

            if ($count === 0) {
                $this->info('No customized settings found to clear.');
                return self::SUCCESS;
            }

            $message = $filter
                ? "This will clear {$count} customized setting(s) matching '{$filter}' in scope '{$scope}'."
                : "This will clear {$count} customized setting(s) in scope '{$scope}'.";

            if (!$force && !$this->confirm($message . ' Continue?', false)) {
                $this->info('Operation cancelled.');
                return self::SUCCESS;
            }

            $query->delete();

            $this->info("Successfully cleared {$count} setting(s).");
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function clearAllScopes(bool $force): int
    {
        $count = Setting::count();

        if ($count === 0) {
            $this->info('No customized settings found to clear.');
            return self::SUCCESS;
        }

        if (!$force && !$this->confirm("This will clear ALL {$count} customized settings across all scopes. Continue?", false)) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        Setting::query()->delete();

        $this->info("Successfully cleared {$count} setting(s) across all scopes.");
        
        return self::SUCCESS;
    }
}
