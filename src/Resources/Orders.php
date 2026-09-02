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

    /**
     * Refund every captured contribution on this order and notify contributors.
     * Blocked once the order has already been included in a payout — the API
     * responds with a 400 in that case.
     *
     * @param  array{reason?: string}|null  $options
     * @return array{message: string}
     */
    public function refund(string $orderId, ?array $options = null): array
    {
        /** @var array{message: string} */
        return $this->http->request(
            'POST',
            '/orders/'.rawurlencode($orderId).'/refund',
            $this->refundBody($options),
        );
    }

    /**
     * Refund a single contributor within this order (a partial refund).
     * Same payout/status guards as {@see refund()}.
     *
     * @param  array{reason?: string}|null  $options
     * @return array{message: string}
     */
    public function refundContribution(string $orderId, string $contributionId, ?array $options = null): array
    {
        /** @var array{message: string} */
        return $this->http->request(
            'POST',
            '/orders/'.rawurlencode($orderId).'/contributions/'.rawurlencode($contributionId).'/refund',
            $this->refundBody($options),
        );
    }

    /**
     * @param  array{reason?: string}|null  $options
     * @return array{reason?: string}
     */
    private function refundBody(?array $options): array
    {
        $reason = isset($options['reason']) ? trim((string) $options['reason']) : '';

        return $reason !== '' ? ['reason' => $reason] : [];
    }
}
