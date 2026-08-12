<?php

namespace QattaPay\Laravel\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Button extends Component
{
    public string $containerId;

    public string $sdkUrl;

    /**
     * @param  string  $intentUrl  POST endpoint that returns `{ "intentId": "…" }`
     * @param  string|null  $mode  `dev` or `live` (ignored when `$baseUrl` is set)
     * @param  string  $openMode  `popup` (default) or `redirect`
     * @param  string|null  $successUrl  Merchant URL after popup `onSuccess` (postMessage)
     * @param  string|null  $returnUrl  Passed to hosted checkout; QattaPay redirects here
     *                                 with `intentId`, `sessionId`, and `status` after payment
     */
    public function __construct(
        public string $intentUrl,
        public ?string $mode = null,
        public string $variant = 'primary',
        public string $label = 'split',
        public string $size = 'md',
        public string $locale = 'en',
        public string $openMode = 'popup',
        public ?string $successUrl = null,
        public ?string $returnUrl = null,
        public bool $showBadge = true,
        public bool $showIcon = true,
        public ?string $baseUrl = null,
        ?string $containerId = null,
    ) {
        $this->mode = $mode ?? (string) config('qattapay.mode', 'dev');
        $this->baseUrl = ($baseUrl !== null && $baseUrl !== '') ? $baseUrl : null;
        $this->containerId = $containerId ?? 'qattapay-checkout-'.bin2hex(random_bytes(4));

        $version = (string) config('qattapay.browser_sdk_version', '1.1.6');
        $this->sdkUrl = 'https://cdn.jsdelivr.net/npm/@hadawi/sdk@'.$version.'/dist/browser.iife.js';
    }

    public function render(): View
    {
        return view('qattapay::components.button');
    }
}
