# Laravel Settings Package - Package Information

## Package Structure

This is a complete, production-ready Laravel Composer package extracted from the DineFusion project.

### Directory Structure

```
Prepare for package/
├── src/
│   ├── Casts/
│   │   └── DynamicTypeCasting.php
│   ├── Models/
│   │   ├── BaseSetting.php
│   │   └── Setting.php
│   ├── Traits/
│   │   └── HasSettings.php
│   └── SettingsServiceProvider.php
├── config/
│   └── settings.php
├── database/
│   └── migrations/
│       └── create_settings_table.php.stub
├── tests/
│   └── SettingTest.php
├── docs/
│   └── Settings.md
├── composer.json
├── phpunit.xml
├── README.md
├── LICENSE
├── CHANGELOG.md
├── CONTRIBUTING.md
├── .gitignore
└── PACKAGE_INFO.md (this file)
```

## Namespace Changes

All classes have been migrated from the application namespace to the package namespace:

- `App\Casts\DynamicTypeCasting` → `YellowParadox\LaravelSettings\Casts\DynamicTypeCasting`
- `App\Models\Settings\BaseSetting` → `YellowParadox\LaravelSettings\Models\BaseSetting`
- `App\Models\Settings\Setting` → `YellowParadox\LaravelSettings\Models\Setting`
- `App\Models\Traits\HasSettings` → `YellowParadox\LaravelSettings\Traits\HasSettings`

## Next Steps to Publish

### 1. Create a Git Repository

```bash
cd "Prepare for package"
git init
git add .
git commit -m "Initial commit: Laravel Settings package v1.0.0"
```

### 2. Create a Bitbucket Repository

1. Go to Bitbucket and create a new repository named `laravel-settings`
2. Push your local repository:

```bash
git remote add origin git@bitbucket.org:jomigomes/laravel-settings.git
git branch -M main
git push -u origin main
```

### 3. Tag a Release

```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### 4. Submit to Packagist

1. Go to https://packagist.org/
2. Sign in with your Bitbucket account (or GitHub account linked to Bitbucket)
3. Click "Submit" and enter your repository URL: `https://bitbucket.org/jomigomes/laravel-settings`
4. Packagist will automatically index your package

### 5. Update composer.json (Optional)

Before publishing, you may want to update:
- Author email in `composer.json`
- Package description if needed
- Keywords for better discoverability

### 6. Set Up Auto-Update Hook

Configure Bitbucket webhook in Packagist settings to automatically update the package when you push new versions.

## Testing Before Publishing

Run tests locally to ensure everything works:

```bash
composer install
composer test
```

## Features Included

✅ Complete source code with proper namespacing
✅ Service provider with auto-discovery
✅ Publishable migration and config files
✅ Comprehensive test suite
✅ Full documentation
✅ README with usage examples
✅ MIT License
✅ Changelog
✅ Contributing guidelines
✅ PHPUnit configuration
✅ .gitignore file

## Package Dependencies

- PHP: ^8.1|^8.2|^8.3
- Laravel: ^10.0|^11.0
- illuminate/database: ^10.0|^11.0
- illuminate/support: ^10.0|^11.0

## Development Dependencies

- orchestra/testbench: ^8.0|^9.0
- phpunit/phpunit: ^10.0|^11.0

## Installation (After Publishing)

Users will be able to install your package with:

```bash
composer require jomigomes/laravel-settings
```

## Package Name

The package is configured as: `jomigomes/laravel-settings`

You can change this in `composer.json` if you prefer a different vendor name or package name.

## Support & Maintenance

Consider setting up:
- Bitbucket Issues for bug reports
- Bitbucket Pipelines for CI/CD
- Code coverage reporting
- Static analysis tools (PHPStan, Psalm)

## License

MIT License - See LICENSE file for details
