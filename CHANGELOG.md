# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Upcoming improvements

### Changed
- Upcoming breaking changes

### Fixed
- Upcoming fixes

## [2.1.0] - 2025-01-08

### Added
- **Events System**: Fire events on setting lifecycle operations
  - `SettingRetrieved` - Fired when a setting is accessed
  - `SettingCreated` - Fired when a new setting is stored
  - `SettingUpdated` - Fired when an existing setting changes
  - `SettingDeleted` - Fired when a setting reverts to default
  - All events include previous value tracking for auditing
- **Settings Facade**: Cleaner API syntax with `Settings::get()` instead of `Setting::get()`
  - Auto-registered via Laravel package discovery
  - Full IDE support with PHPDoc annotations
- **Artisan Commands**: CLI tools for settings management
  - `settings:list` - View all settings or filter by scope/group
  - `settings:clear` - Reset customized settings to defaults
  - Both commands support filtering and confirmation prompts
- **Caching Layer**: Optional performance optimization
  - Configurable via `SETTINGS_CACHE_ENABLED` environment variable
  - Adjustable TTL via `SETTINGS_CACHE_TTL` (default: 3600 seconds)
  - Automatic cache invalidation on updates
  - Works with all Laravel cache drivers

### Changed
- Service provider now conditionally registers cached service implementation
- Configuration file includes new cache settings section

## [2.0.0] - 2025-01-08

### Breaking
- `Setting::get`, `Setting::set`, `Setting::getAllScoped`, `Setting::getFiltered`, and the `HasSettings` trait helpers now return `SettingData` DTOs instead of `stdClass`/`Setting` models.
- Introduced service, repository, and validator layers behind dependency injection bindings, removing the deprecated `BaseSetting` class.

### Added
- `SettingData` immutable DTO to provide consistent object-like behaviour for default and persisted settings.
- `SettingsRepository`, `SettingsService`, and `SettingValidator` layers with contracts for clean architecture and easier testing.
- Database indexes for `scope`, `setting`, and polymorphic columns to improve query performance.
- Secure `SecureDynamicTypeCasting` cast that uses JSON-based serialization for objects.
- Extensive feature tests for model and non-model scopes, plus validator unit tests.
- `REFACTORING_SUMMARY.md` documenting the new architecture.

### Changed
- Service provider now registers repository/service/validator bindings and publishes a timestamped migration.
- Migration file published with indexes; legacy `.stub` kept for BC but not auto-loaded.
- Documentation updated to reflect new architecture and DTO return type.

### Fixed
- Removed insecure `unserialize` usage when casting objects.
- Ensured default and persisted settings share the same API surface via DTOs.

## [1.0.0] - 2025-01-08

Initial release (superseded by 2.0.0 refactor)
