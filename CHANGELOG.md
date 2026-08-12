# Changelog

All notable changes to `qattapay/laravel` are documented here.

## 1.0.0 — 2026-08-12

Initial public release.

- Server client: intents, orders, webhook verification (parity with `@hadawi/sdk`)
- Laravel service provider, config, and `QattaPay` facade
- `VerifyQattaPayWebhook` middleware (`qattapay.webhook` alias)
- Blade `<x-qattapay-button>` component (loads `@hadawi/sdk` browser IIFE from jsDelivr)
- Multi-host API fallback (`qatta.sa` → `hadawi.sa`) on network errors
