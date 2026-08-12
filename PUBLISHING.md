# Publishing `qattapay/laravel`

Public repo: https://github.com/Hadawi-Engineering/qattapay-laravel

## Prerequisites (one-time)

1. Submit the package on [Packagist](https://packagist.org/packages/submit):
   - URL: `https://github.com/Hadawi-Engineering/qattapay-laravel`
   - Auto-updates via GitHub hook (enable when prompted)
2. Optional CI secrets (only if using the Packagist notify step):
   - `PACKAGIST_USERNAME`
   - `PACKAGIST_TOKEN`

## Release flow

1. Bump `CHANGELOG.md`.

2. Commit on `main`:

   ```bash
   git add -A
   git commit -m "chore: release vX.Y.Z"
   git push origin main
   ```

3. Tag and push (triggers `.github/workflows/publish.yml`):

   ```bash
   git tag vX.Y.Z
   git push origin vX.Y.Z
   ```

   Tags must be semver: `v1.0.0`, `v1.1.0`, …

4. Packagist indexes the tag automatically (GitHub service hook) or via the workflow webhook.

## Local checks

```bash
composer install
composer test
composer validate --strict
composer archive --format=zip --file=dist/qattapay-laravel
```

## Install

```bash
composer require qattapay/laravel
```
