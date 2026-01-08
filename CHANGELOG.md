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

## [2.0.0] - TBD

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

## [1.0.0] - TBD

Initial release
