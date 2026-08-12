<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Merchant API key from the QattaPay dashboard (Developer → API Keys).
    | Use a `dev` key with mode=dev and a `live` key with mode=live.
    |
    */

    'api_key' => env('QATTAPAY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Per-merchant webhook signing secret (`whsec_…`) from the dashboard
    | (Developer → Webhook → Reveal). Required to verify inbound webhooks.
    |
    */

    'webhook_secret' => env('QATTAPAY_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Mode
    |--------------------------------------------------------------------------
    |
    | `dev`  → https://dev.qatta.sa (with hadawi.sa fallback)
    | `live` → https://qatta.sa (with beta.hadawi.sa fallback)
    |
    | Ignored when `base_url` is set.
    |
    */

    'mode' => env('QATTAPAY_MODE', 'dev'),

    /*
    |--------------------------------------------------------------------------
    | Base URL Override
    |--------------------------------------------------------------------------
    |
    | Optional explicit API base (e.g. http://localhost:4000). When set, mode
    | host resolution and fallback are skipped.
    |
    */

    'base_url' => env('QATTAPAY_BASE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Browser SDK Version
    |--------------------------------------------------------------------------
    |
    | npm version of `@hadawi/sdk` loaded from jsDelivr for the Blade button.
    | Keep in sync with the published TypeScript browser SDK.
    |
    */

    'browser_sdk_version' => env('QATTAPAY_BROWSER_SDK_VERSION', '1.1.6'),

];
