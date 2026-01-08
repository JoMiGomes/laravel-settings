# Publishing Guide for Laravel Settings Package

## ✅ Pre-Publication Checklist

All items below have been verified and are ready:

- [x] **Package Name**: `jomigomes/laravel-settings` (available on Packagist)
- [x] **Version**: 2.1.0
- [x] **License**: MIT (LICENSE file present)
- [x] **Author**: João Gomes
- [x] **Tests**: All 73 tests passing ✅
- [x] **Documentation**: README.md, CHANGELOG.md, Settings.md complete
- [x] **Namespace**: JomiGomes\LaravelSettings
- [x] **PHP Support**: ^8.1|^8.2|^8.3
- [x] **Laravel Support**: ^10.0|^11.0
- [x] **Service Provider**: Auto-discovery configured
- [x] **Facade**: Auto-registered
- [x] **Git**: All changes committed

## 📦 Step-by-Step Publishing Process

### Step 1: Create a Packagist Account

1. Go to https://packagist.org/
2. Click "Sign Up" or "Login with GitHub" (recommended)
3. Verify your email address

### Step 2: Prepare Your GitHub Repository

**IMPORTANT**: Your package must be in a public GitHub repository.

```bash
# If you haven't already, push to GitHub
git remote add origin https://github.com/YOUR_USERNAME/laravel-settings.git
git branch -M main
git push -u origin main
```

Replace `YOUR_USERNAME` with your actual GitHub username.

### Step 3: Tag Your Release

Create a git tag for version 2.1.0:

```bash
# Create annotated tag
git tag -a v2.1.0 -m "Release version 2.1.0 - Events, Facade, Commands, Caching"

# Push the tag to GitHub
git push origin v2.1.0

# Verify tag was created
git tag -l
```

### Step 4: Create GitHub Release (Optional but Recommended)

1. Go to your repository on GitHub
2. Click "Releases" → "Create a new release"
3. Select tag: `v2.1.0`
4. Release title: `v2.1.0 - Events, Facade, Commands & Caching`
5. Description: Copy from CHANGELOG.md (v2.1.0 section)
6. Click "Publish release"

### Step 5: Submit to Packagist

1. Go to https://packagist.org/packages/submit
2. Enter your repository URL: `https://github.com/YOUR_USERNAME/laravel-settings`
3. Click "Check"
4. Review the package information
5. Click "Submit"

**That's it!** Packagist will:
- Index your package
- Make it available via `composer require jomigomes/laravel-settings`
- Auto-update when you push new tags

### Step 6: Set Up Auto-Update Hook (Recommended)

To automatically update Packagist when you push to GitHub:

1. Go to your package on Packagist: `https://packagist.org/packages/jomigomes/laravel-settings`
2. Click your username → "Your packages"
3. Click on your package
4. Copy the "GitHub Service Hook" URL
5. Go to your GitHub repository → Settings → Webhooks → Add webhook
6. Paste the Packagist URL
7. Content type: `application/json`
8. Click "Add webhook"

Now Packagist will auto-update whenever you push tags!

## 🚀 Publishing Future Versions

For future releases:

```bash
# 1. Make your changes and commit
git add .
git commit -m "feat: your new feature"

# 2. Update version in composer.json
# Edit: "version": "2.2.0"

# 3. Update CHANGELOG.md
# Add new version section with changes

# 4. Commit version bump
git add .
git commit -m "chore: bump version to 2.2.0"

# 5. Create and push tag
git tag -a v2.2.0 -m "Release version 2.2.0"
git push origin main
git push origin v2.2.0

# 6. Packagist auto-updates (if webhook configured)
```

## 📊 Post-Publication

After publishing:

1. **Test Installation**:
   ```bash
   composer create-project laravel/laravel test-app
   cd test-app
   composer require jomigomes/laravel-settings
   ```

2. **Add Package Badges to README** (optional):
   ```markdown
   [![Latest Version](https://img.shields.io/packagist/v/jomigomes/laravel-settings.svg)](https://packagist.org/packages/jomigomes/laravel-settings)
   [![Total Downloads](https://img.shields.io/packagist/dt/jomigomes/laravel-settings.svg)](https://packagist.org/packages/jomigomes/laravel-settings)
   [![License](https://img.shields.io/packagist/l/jomigomes/laravel-settings.svg)](https://packagist.org/packages/jomigomes/laravel-settings)
   ```

3. **Monitor**:
   - Check Packagist stats: https://packagist.org/packages/jomigomes/laravel-settings/stats
   - Watch for issues on GitHub
   - Respond to community feedback

## 🔒 Security

If users find security vulnerabilities:

1. Add SECURITY.md file with reporting instructions
2. Consider using GitHub Security Advisories
3. Release patches quickly for security issues

## 📝 Important Notes

- **Semantic Versioning**: Follow SemVer (MAJOR.MINOR.PATCH)
  - MAJOR: Breaking changes (3.0.0)
  - MINOR: New features, backward compatible (2.2.0)
  - PATCH: Bug fixes (2.1.1)

- **Composer Version Field**: The `version` field in composer.json is optional. Packagist uses git tags as the source of truth. However, keeping it in sync is good practice.

- **Minimum Stability**: Your package has `"minimum-stability": "dev"` and `"prefer-stable": true`, which is fine for development but users will get stable releases.

## ✅ Your Package is Ready!

Everything is in place. Just follow Steps 2-5 above to publish.

Good luck with your package! 🎉
