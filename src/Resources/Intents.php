<?php

namespace QattaPay\Laravel\Resources;

use QattaPay\Laravel\Http\ApiClient;

/**
 * Wraps the `/intents` API endpoints.
 */
class Intents
{
    public function __construct(
        private readonly ApiClient $http,
    ) {}

    /**
     * Create a checkout intent.
     *
     * Returns an `{ intent, redirectUrl }` pair — send `redirectUrl` to the
     * browser or pass `intent.id` to the Blade checkout button / browser SDK.
     *
     * Amounts are in the currency's smallest unit (halalas for SAR).
     *
     * @param  array{
     *     itemSnapshot: list<array{name: string, nameAr?: string, price: int, image?: string, reference?: string}>,
     *     totalAmount: int,
     *     currency?: string,
     *     metadata?: array<string, mixed>
     * }  $params
     * @return array{intent: array<string, mixed>, redirectUrl: string}
     */
    public function create(array $params): array
    {
        /** @var array{intent: array<string, mixed>, redirectUrl: string} */
        return $this->http->request('POST', '/intents', $params);
    }

    /**
     * Fetch details of an existing intent.
     *
     * @return array{intent: array<string, mixed>}
     */
    public function get(string $intentId): array
    {
        /** @var array{intent: array<string, mixed>} */
        return $this->http->request('GET', '/intents/'.rawurlencode($intentId));
    }
}
