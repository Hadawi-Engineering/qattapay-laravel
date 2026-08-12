# Publishing `qattapay/laravel`

Public repo: https://github.com/Hadawi-Engineering/qattapay-laravel  
Packagist: https://packagist.org/packages/qattapay/laravel

## Prerequisites (one-time)

### 1. Packagist package

Already submitted as `qattapay/laravel`.

### 2. GitHub → Packagist webhook (required for auto-updates)

In the repo: **Settings → Webhooks → Add webhook**

| Field | Value |
| ----- | ----- |
| Payload URL | `https://packagist.org/api/github?username=qattapay` |
| Content type | `application/json` |
| Secret | your Packagist **API Token** (from [profile](https://packagist.org/profile/)) |
| Which events? | Just the **`push`** event |
| Active | checked |

Or grant Packagist GitHub App access when logged into Packagist via GitHub (orgs + repos).

### 3. Optional CI secrets

For the workflow’s Packagist notify step (backup if the GitHub hook fails):

- `PACKAGIST_USERNAME` = `qattapay`
- `PACKAGIST_TOKEN` = same API token as the webhook secret

## Release flow

1. Bump `CHANGELOG.md`.

2. Commit on `main`:

   ```bash
   git add -A
   git commit -m "chore: release vX.Y.Z"
   git push origin main
   ```

3. Tag and push (triggers `.github/workflows/publish.yml` + Packagist hook):

   ```bash
   git tag vX.Y.Z
   git push origin vX.Y.Z
   ```

   Tags must be semver: `v1.0.0`, `v1.1.0`, …

## Manual Packagist refresh

```bash
curl -XPOST -H 'Content-Type:application/json' \
  'https://packagist.org/api/update-package?username=qattapay&apiToken=API_TOKEN' \
  -d '{"repository":{"url":"https://github.com/Hadawi-Engineering/qattapay-laravel"}}'
```

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
