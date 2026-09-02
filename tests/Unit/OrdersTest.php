<?php

namespace QattaPay\Laravel\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use QattaPay\Laravel\QattaPayClient;
use QattaPay\Laravel\QattaPayServiceProvider;

class OrdersTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [QattaPayServiceProvider::class];
    }

    private function client(): QattaPayClient
    {
        return new QattaPayClient([
            'api_key' => 'test_key',
            'mode' => 'dev',
        ]);
    }

    public function test_fulfill_and_deliver(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/orders/ord_1/fulfill' => Http::response([
                'order' => ['id' => 'ord_1', 'status' => 'fulfilling'],
            ], 200),
            'https://dev.qatta.sa/api/orders/ord_1/deliver' => Http::response([
                'order' => ['id' => 'ord_1', 'status' => 'delivered'],
            ], 200),
        ]);

        $qatta = $this->client();

        $fulfilled = $qatta->orders()->fulfill('ord_1');
        $this->assertSame('fulfilling', $fulfilled['order']['status']);

        $delivered = $qatta->orders()->deliver('ord_1');
        $this->assertSame('delivered', $delivered['order']['status']);

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/orders/ord_1/fulfill'));
        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/orders/ord_1/deliver'));
    }

    public function test_refund_sends_reason(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/orders/ord_1/refund' => Http::response([
                'message' => 'Refund initiated',
            ], 200),
        ]);

        $result = $this->client()->orders()->refund('ord_1', ['reason' => 'Out of stock']);

        $this->assertSame('Refund initiated', $result['message']);

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/orders/ord_1/refund')
                && $request['reason'] === 'Out of stock';
        });
    }

    public function test_refund_contribution_omits_empty_reason(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/orders/ord_1/contributions/c_9/refund' => Http::response([
                'message' => 'Refund initiated',
            ], 200),
        ]);

        $this->client()->orders()->refundContribution('ord_1', 'c_9');

        Http::assertSent(function ($request) {
            return $request->method() === 'POST'
                && str_ends_with($request->url(), '/orders/ord_1/contributions/c_9/refund')
                && ! $request->has('reason');
        });
    }
}
