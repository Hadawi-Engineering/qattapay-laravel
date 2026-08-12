<?php

namespace QattaPay\Laravel;

use QattaPay\Laravel\Http\ApiClient;
use QattaPay\Laravel\Resources\Intents;
use QattaPay\Laravel\Resources\Orders;
use QattaPay\Laravel\Support\Hosts;
use QattaPay\Laravel\Webhooks\Webhooks;

/**
 * Main server-side QattaPay SDK client.
 *
 * Initialise once (e.g. via the service container) and reuse across requests.
 *
 * @example
 * ```php
 * $qattapay = new QattaPayClient([
 *     'api_key' => env('QATTAPAY_API_KEY'),
 *     'mode' => 'live', // or 'dev'
 *     'webhook_secret' => env('QATTAPAY_WEBHOOK_SECRET'),
 * ]);
 * ```
 */
class QattaPayClient
{
    public readonly Intents $intents;

    public readonly Orders $orders;

    public readonly Webhooks $webhooks;

    /**
     * @param  array{
     *     api_key: string,
     *     mode?: string,
     *     base_url?: string|null,
     *     webhook_secret?: string|null
     * }  $config
     */
    public function __construct(array $config)
    {
        $apiKey = $config['api_key'] ?? '';
        if ($apiKey === '') {
            throw new \InvalidArgumentException(
                '[QattaPayClient] `api_key` is required.'
            );
        }

        $baseUrl = $config['base_url'] ?? null;
        $mode = $config['mode'] ?? null;

        if (! $baseUrl && ! $mode) {
            throw new \InvalidArgumentException(
                '[QattaPayClient] `mode` is required ("dev" | "live"). '.
                'Pass mode: "dev" for testing or mode: "live" for production.'
            );
        }

        // When `mode` is used, try qatta.sa first and fall back to hadawi.sa
        // if unreachable. An explicit `base_url` override is used as-is
        // (same as the TypeScript SDK — no automatic `/api` suffix).
        /** @var string|list<string> $baseUrls */
        $baseUrls = $baseUrl
            ? rtrim((string) $baseUrl, '/')
            : Hosts::resolveApiHosts((string) $mode);

        $http = new ApiClient($baseUrls, $apiKey);

        $this->intents = new Intents($http);
        $this->orders = new Orders($http);
        $this->webhooks = new Webhooks((string) ($config['webhook_secret'] ?? ''));
    }

    public function intents(): Intents
    {
        return $this->intents;
    }

    public function orders(): Orders
    {
        return $this->orders;
    }

    public function webhooks(): Webhooks
    {
        return $this->webhooks;
    }
}
