<?php

namespace QattaPay\Laravel\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QattaPay\Laravel\Exceptions\WebhookException;
use QattaPay\Laravel\Webhooks\Webhooks;

class WebhooksTest extends TestCase
{
    private const SECRET = 'whsec_test_secret';

    public function test_verify_valid_signature(): void
    {
        $body = '{"event":"order.funded","session_id":"s1","merchant_id":"m1","items":[],"total_amount":100,"currency":"SAR","funded_at":"2026-01-01T00:00:00Z"}';
        $sig = hash_hmac('sha256', $body, self::SECRET);

        $webhooks = new Webhooks(self::SECRET);
        $this->assertTrue($webhooks->verifySignature($body, $sig));
    }

    public function test_verify_rejects_invalid_signature(): void
    {
        $webhooks = new Webhooks(self::SECRET);
        $this->assertFalse($webhooks->verifySignature('{"event":"order.funded"}', 'deadbeef'));
    }

    public function test_construct_event(): void
    {
        $body = '{"event":"order.funded","session_id":"s1","merchant_id":"m1","items":[],"total_amount":100,"currency":"SAR","funded_at":"2026-01-01T00:00:00Z","order_id":"o1"}';
        $sig = hash_hmac('sha256', $body, self::SECRET);

        $event = (new Webhooks(self::SECRET))->constructEvent($body, $sig);

        $this->assertSame('order.funded', $event['type']);
        $this->assertSame('o1', $event['payload']['order_id']);
    }

    public function test_construct_event_throws_on_bad_signature(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid webhook signature');

        (new Webhooks(self::SECRET))->constructEvent('{"event":"order.funded"}', '00');
    }

    public function test_missing_secret_throws(): void
    {
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('webhookSecret is required');

        (new Webhooks(''))->verifySignature('{}', 'abc');
    }
}
