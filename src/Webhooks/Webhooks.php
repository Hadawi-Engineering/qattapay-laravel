<?php

namespace QattaPay\Laravel\Webhooks;

use QattaPay\Laravel\Exceptions\WebhookException;

/**
 * Utilities for verifying and parsing incoming QattaPay webhook events.
 *
 * QattaPay signs every outgoing webhook POST with HMAC-SHA256 over the raw
 * JSON body using your `webhookSecret`. The hex digest is sent in the
 * `X-QattaPay-Signature` request header.
 */
class Webhooks
{
    public function __construct(
        private readonly string $webhookSecret,
    ) {}

    /**
     * Verify the `X-QattaPay-Signature` header against the raw request body.
     */
    public function verifySignature(string $rawBody, string $signature): bool
    {
        if ($this->webhookSecret === '') {
            throw new WebhookException(
                'webhookSecret is required to verify webhook signatures. '.
                'Pass it as `webhook_secret` in config/qattapay.php or QattaPayClient config.'
            );
        }

        if ($signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $this->webhookSecret);

        // Compare hex digests in constant time (case-insensitive for hex).
        return hash_equals(strtolower($expected), strtolower($signature));
    }

    /**
     * Verify the signature and parse the raw body into a webhook event.
     *
     * Important: pass the raw request body — do not re-encode JSON before
     * verification.
     *
     * @return array{type: string, payload: array<string, mixed>}
     *
     * @throws WebhookException
     */
    public function constructEvent(string $rawBody, string $signature): array
    {
        if (! $this->verifySignature($rawBody, $signature)) {
            throw new WebhookException('Invalid webhook signature');
        }

        /** @var array<string, mixed>|null $parsed */
        $parsed = json_decode($rawBody, true);

        if (! is_array($parsed) || ! isset($parsed['event']) || ! is_string($parsed['event'])) {
            throw new WebhookException('Invalid webhook payload');
        }

        return [
            'type' => $parsed['event'],
            'payload' => $parsed,
        ];
    }
}
