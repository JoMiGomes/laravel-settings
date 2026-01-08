<?php

namespace JomiGomes\LaravelSettings\Services;

use JomiGomes\LaravelSettings\DataTransferObjects\SettingData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CachedSettingsService extends SettingsService
{
    private const CACHE_PREFIX = 'settings:';

    private function getCacheTtl(): int
    {
        return config('settings.cache.ttl', 3600);
    }

    public function get(string $setting, Model|string $scope): SettingData|null
    {
        $cacheKey = $this->getCacheKey($setting, $scope);

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($setting, $scope) {
            return parent::get($setting, $scope);
        });
    }

    public function set(string $setting, mixed $newValue, Model|string $scope): SettingData
    {
        $result = parent::set($setting, $newValue, $scope);
        
        $this->clearCache($setting, $scope);
        $this->clearScopeCache($scope);
        
        return $result;
    }

    public function getAllScoped(Model|string $scope): Collection
    {
        $cacheKey = $this->getScopeCacheKey($scope, 'all');

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($scope) {
            return parent::getAllScoped($scope);
        });
    }

    public function getFiltered(Model|string $scope, string $filter): Collection
    {
        $cacheKey = $this->getScopeCacheKey($scope, "filtered:{$filter}");

        return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($scope, $filter) {
            return parent::getFiltered($scope, $filter);
        });
    }

    public function clearCache(?string $setting = null, Model|string|null $scope = null): void
    {
        if ($setting && $scope) {
            Cache::forget($this->getCacheKey($setting, $scope));
        } elseif ($scope) {
            $this->clearScopeCache($scope);
        } else {
            Cache::tags([self::CACHE_PREFIX])->flush();
        }
    }

    private function getCacheKey(string $setting, Model|string $scope): string
    {
        $scopeString = $scope instanceof Model 
            ? $scope->getSettingsScope() . ':' . $scope->getKey()
            : $scope;

        return self::CACHE_PREFIX . "{$scopeString}:{$setting}";
    }

    private function getScopeCacheKey(Model|string $scope, string $suffix): string
    {
        $scopeString = $scope instanceof Model 
            ? $scope->getSettingsScope() . ':' . $scope->getKey()
            : $scope;

        return self::CACHE_PREFIX . "{$scopeString}:{$suffix}";
    }

    private function clearScopeCache(Model|string $scope): void
    {
        // Clear common cache keys for the scope
        Cache::forget($this->getScopeCacheKey($scope, 'all'));
        
        // Note: For filtered caches, they will expire naturally based on TTL
        // For complete cache clearing, use clearCache() without parameters
    }
}
