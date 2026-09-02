# QattaPay Laravel SDK

Official [QattaPay](https://qatta.sa) SDK for Laravel — add group contribution checkout to any storefront.

```bash
composer require qattapay/laravel
```

QattaPay lets groups of people split the cost of a purchase. This package handles:

- **Server-side**: creating checkout intents and managing orders with your merchant API key
- **Webhooks**: verifying and parsing events when a session is funded
- **Blade**: official branded checkout buttons (via the `@hadawi/sdk` browser bundle)

Parity with the Node SDK [`@hadawi/sdk`](https://www.npmjs.com/package/@hadawi/sdk).

---

## Requirements

- PHP 8.2+
- Laravel 10, 11, or 12

---

## Installation

```bash
composer require qattapay/laravel
```

The service provider and `QattaPay` facade are auto-discovered.

Publish the config (optional):

```bash
php artisan vendor:publish --tag=qattapay-config
```

---

## Configuration

Add to your `.env`:

```env
QATTAPAY_API_KEY=your_api_key
QATTAPAY_WEBHOOK_SECRET=whsec_...
QATTAPAY_MODE=dev
# QATTAPAY_BASE_URL=http://localhost:4000   # optional local override
# QATTAPAY_BROWSER_SDK_VERSION=1.1.6        # jsDelivr pin for the button
```

| Key | Description |
| --- | --- |
| `api_key` | Merchant API key from the QattaPay dashboard |
| `webhook_secret` | `whsec_…` signing secret (Developer → Webhook) |
| `mode` | `dev` or `live` (ignored if `base_url` is set) |
| `base_url` | Explicit API base (e.g. local) — disables host fallback |
| `browser_sdk_version` | `@hadawi/sdk` version loaded for `<x-qattapay-button>` |

Amounts are always integers in the **smallest currency unit** (halalas for SAR — e.g. `15000` = 150.00 SAR).

---

## Quick start

### 1 — Create an intent (server)

```php
use QattaPay\Laravel\Facades\QattaPay;

Route::post('/qattapay/intent', function () {
    $result = QattaPay::intents()->create([
        'itemSnapshot' => [
            [
                'name' => 'Luxury Watch',
                'price' => 150000,
                'reference' => 'watch-001',
            ],
        ],
        'totalAmount' => 150000,
        'currency' => 'SAR',
        'metadata' => ['cart_id' => 'abc'],
    ]);

    return [
        'intentId' => $result['intent']['id'],
    ];
})->middleware('web')->name('qattapay.intent');
```

### 2 — Mount the branded button (Blade)

```blade
{{-- Popup: success-url runs after qattapay:success postMessage --}}
<x-qattapay-button
    intent-url="{{ route('qattapay.intent') }}"
    mode="{{ config('qattapay.mode') }}"
    variant="primary"
    label="split"
    open-mode="popup"
    success-url="{{ url('/thank-you') }}"
    return-url="{{ url('/thank-you') }}"
/>
```

```blade
{{-- Redirect: hosted checkout sends the shopper back to return-url --}}
<x-qattapay-button
    intent-url="{{ route('qattapay.intent') }}"
    mode="{{ config('qattapay.mode') }}"
    variant="primary"
    label="split"
    open-mode="redirect"
    return-url="{{ url('/thank-you') }}"
/>
```

| Attribute | Purpose |
| --- | --- |
| `success-url` | After popup `onSuccess` (`qattapay:success`), navigate here |
| `return-url` | Passed to hosted checkout as `?returnUrl=`. After payment, QattaPay redirects here with `intentId`, `sessionId`, and `status=success\|cancel\|failed`. Use for **redirect** mode (and as a popup fallback). |
| `open-mode` | `popup` (default) or `redirect` |

Ensure your layout includes the CSRF meta tag:

```blade
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 3 — Handle webhooks

```php
use Illuminate\Http\Request;
use QattaPay\Laravel\Facades\QattaPay;

Route::post('/webhooks/qattapay', function (Request $request) {
    $event = $request->attributes->get('qattapay_event');

    if ($event['type'] === 'order.funded') {
        $orderId = $event['payload']['order_id'] ?? null;
        if ($orderId) {
            QattaPay::orders()->fulfill($orderId);
        }
    }

    return response()->noContent();
})->middleware('qattapay.webhook');
```

---

## API reference

### Intents

```php
QattaPay::intents()->create([/* ... */]);
QattaPay::intents()->get($intentId);
```

### Orders

These map to the same dashboard actions as **Mark as Fulfilling** / **Mark as Delivered**. Call them from your backend — they are not optional for merchants who want their own OMS in sync, but QattaPay does not require them before payout.

```php
// List all orders
QattaPay::orders()->list();

// Get detail (includes contribution breakdown)
QattaPay::orders()->get($orderId);

// funded / notified → fulfilling
QattaPay::orders()->fulfill($orderId);

// fulfilling → delivered
QattaPay::orders()->deliver($orderId);

// Refund every captured contribution (blocked once the order is in a payout)
QattaPay::orders()->refund($orderId, ['reason' => 'Out of stock']);

// Refund a single contributor
QattaPay::orders()->refundContribution($orderId, $contributionId, [
    'reason' => 'Contributor requested to back out',
]);
```

Order status lifecycle:

```
pending_funding → funded → notified → fulfilling → delivered
                         ↘ cancelled
```

`fulfill()` only works from `funded` or `notified`. `deliver()` only works from `fulfilling`.

Refunds process synchronously against the original payment method and notify each affected contributor. They are rejected (HTTP 400) when the order is already in a payout, or the session is already `refunding` / `refunded` / `cancelled`.

### Webhooks

```php
QattaPay::webhooks()->verifySignature($rawBody, $signature);
QattaPay::webhooks()->constructEvent($rawBody, $signature);
```

Event types: `order.funded`, `order.partially_funded`, `order.cancelled`, `order.expired`.

---

## Host resolution

| Mode | Primary | Fallback (network errors only) |
| ---- | ------- | ------------------------------ |
| `dev` | `https://dev.qatta.sa/api` | `https://dev.hadawi.sa/api` |
| `live` | `https://qatta.sa/api` | `https://beta.hadawi.sa/api` |

---

## Development

```bash
composer install
composer test
```

## Publishing

Package: [packagist.org/packages/qattapay/laravel](https://packagist.org/packages/qattapay/laravel)  
Latest release: [`v1.1.1`](https://github.com/Hadawi-Engineering/qattapay-laravel/releases/tag/v1.1.1)

To ship a new version, bump the changelog, push `main`, then tag:

```bash
git tag v1.1.1
git push origin v1.1.1
```

Packagist updates automatically via the GitHub webhook. Full checklist: [PUBLISHING.md](./PUBLISHING.md).

---

## License

MIT © Hadawi Engineering
