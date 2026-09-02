# Changelog

All notable changes to `qattapay/laravel` are documented here.

## 1.1.1 — 2026-09-02

### Fixed

- Orders test no longer calls a non-existent `Request::has()` on Laravel's HTTP client fake.

## 1.1.0 — 2026-09-02

### Added

- **`QattaPay::orders()->refund($orderId, ['reason' => …])`** — refund every captured contribution on an order (parity with `@hadawi/sdk` `orders.refund()`).
- **`QattaPay::orders()->refundContribution($orderId, $contributionId, ['reason' => …])`** — refund a single contributor (parity with `orders.refundContribution()`).

### Docs

- Document order status transitions (`fulfill` / `deliver`) and refunds in the README.

## 1.0.1 — 2026-08-12

### Docs

- Document `return-url` vs `success-url` on `<x-qattapay-button>`
- Clarify hosted checkout redirect back to the merchant with `intentId`, `sessionId`, and `status`
- Add redirect-mode Blade example

### Changed

- Default jsDelivr pin for `@hadawi/sdk` browser bundle: `1.1.6`

## 1.0.0 — 2026-08-12

Initial public release.

- Server client: intents, orders, webhook verification (parity with `@hadawi/sdk`)
- Laravel service provider, config, and `QattaPay` facade
- `VerifyQattaPayWebhook` middleware (`qattapay.webhook` alias)
- Blade `<x-qattapay-button>` component (loads `@hadawi/sdk` browser IIFE from jsDelivr)
- Multi-host API fallback (`qatta.sa` → `hadawi.sa`) on network errors
