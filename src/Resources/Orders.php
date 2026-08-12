<?php

namespace QattaPay\Laravel\Resources;

use QattaPay\Laravel\Http\ApiClient;

/**
 * Wraps the `/orders` API endpoints (merchant-authenticated).
 */
class Orders
{
    public function __construct(
        private readonly ApiClient $http,
    ) {}

    /**
     * List all orders for the authenticated merchant.
     *
     * @return array{orders: list<array<string, mixed>>}
     */
    public function list(): array
    {
        /** @var array{orders: list<array<string, mixed>>} */
        return $this->http->request('GET', '/orders');
    }

    /**
     * Fetch a single order with its full contribution breakdown.
     *
     * @return array<string, mixed>
     */
    public function get(string $orderId): array
    {
        /** @var array<string, mixed> */
        return $this->http->request('GET', '/orders/'.rawurlencode($orderId));
    }

    /**
     * Transition the order from `funded` / `notified` → `fulfilling`.
     *
     * @return array<string, mixed>
     */
    public function fulfill(string $orderId): array
    {
        /** @var array<string, mixed> */
        return $this->http->request('PATCH', '/orders/'.rawurlencode($orderId).'/fulfill');
    }

    /**
     * Transition the order from `fulfilling` → `delivered`.
     *
     * @return array<string, mixed>
     */
    public function deliver(string $orderId): array
    {
        /** @var array<string, mixed> */
        return $this->http->request('PATCH', '/orders/'.rawurlencode($orderId).'/deliver');
    }
}
