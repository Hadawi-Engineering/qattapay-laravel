<?php

namespace QattaPay\Laravel\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use QattaPay\Laravel\Exceptions\ApiException;
use QattaPay\Laravel\Http\ApiClient;
use QattaPay\Laravel\QattaPayClient;
use QattaPay\Laravel\QattaPayServiceProvider;

class ApiClientTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [QattaPayServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('qattapay.api_key', 'test_key');
        $app['config']->set('qattapay.mode', 'dev');
        $app['config']->set('qattapay.webhook_secret', 'whsec_test');
    }

    public function test_successful_json_request(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/intents' => Http::response([
                'intent' => ['id' => 'intent_1'],
                'redirectUrl' => 'https://dev.qatta.sa/checkout/intent_1',
            ], 201),
        ]);

        $client = new ApiClient(['https://dev.qatta.sa/api'], 'test_key');
        $result = $client->request('POST', '/intents', [
            'itemSnapshot' => [['name' => 'Watch', 'price' => 100]],
            'totalAmount' => 100,
        ]);

        $this->assertSame('intent_1', $result['intent']['id']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key', 'test_key')
                && $request->url() === 'https://dev.qatta.sa/api/intents';
        });
    }

    public function test_api_error_throws_with_code(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/orders' => Http::response([
                'error' => ['message' => 'Unauthorized', 'code' => 'unauthorized'],
            ], 401),
        ]);

        $client = new ApiClient('https://dev.qatta.sa/api', 'bad_key');

        try {
            $client->request('GET', '/orders');
            $this->fail('Expected ApiException');
        } catch (ApiException $e) {
            $this->assertSame(401, $e->status);
            $this->assertSame('unauthorized', $e->getErrorCode());
            $this->assertSame('Unauthorized', $e->getMessage());
        }
    }

    public function test_falls_back_on_connection_error(): void
    {
        Http::fake(function ($request) {
            if (str_contains($request->url(), 'qatta.sa')) {
                throw new \Illuminate\Http\Client\ConnectionException('Connection refused');
            }

            return Http::response(['orders' => []], 200);
        });

        $client = new ApiClient([
            'https://dev.qatta.sa/api',
            'https://dev.hadawi.sa/api',
        ], 'test_key');

        $result = $client->request('GET', '/orders');
        $this->assertSame([], $result['orders']);
    }

    public function test_qatta_pay_client_intents_create(): void
    {
        Http::fake([
            'https://dev.qatta.sa/api/intents' => Http::response([
                'intent' => ['id' => 'intent_abc'],
                'redirectUrl' => 'https://dev.qatta.sa/checkout/intent_abc',
            ], 201),
        ]);

        $qatta = new QattaPayClient([
            'api_key' => 'test_key',
            'mode' => 'dev',
        ]);

        $result = $qatta->intents()->create([
            'itemSnapshot' => [['name' => 'Watch', 'price' => 150000]],
            'totalAmount' => 150000,
            'currency' => 'SAR',
        ]);

        $this->assertSame('intent_abc', $result['intent']['id']);
    }

    public function test_client_requires_mode_or_base_url(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new QattaPayClient(['api_key' => 'k']);
    }
}
