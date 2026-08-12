<?php

namespace QattaPay\Laravel\Http;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use QattaPay\Laravel\Exceptions\ApiException;

/**
 * Lightweight HTTP wrapper shared by all server-side resources.
 * Uses the `X-API-Key` header for merchant authentication.
 *
 * When multiple base URLs are provided, hosts after the first are only used
 * on network-level failure (DNS / connection / timeout). Application-level
 * 4xx/5xx responses do not trigger a fallback attempt.
 */
class ApiClient
{
    /**
     * @param  string|list<string>  $baseUrls
     */
    public function __construct(
        private readonly string|array $baseUrls,
        private readonly string $apiKey,
    ) {}

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, scalar|null>|null  $query
     * @return array<string, mixed>|null
     *
     * @throws ApiException
     */
    public function request(
        string $method,
        string $path,
        ?array $body = null,
        ?array $query = null,
    ): ?array {
        $hosts = is_array($this->baseUrls) ? $this->baseUrls : [$this->baseUrls];
        $lastNetworkError = null;

        foreach ($hosts as $host) {
            $url = rtrim($host, '/').$path;

            try {
                $pending = $this->pending();

                if ($query !== null) {
                    $filtered = array_filter(
                        $query,
                        static fn ($value) => $value !== null
                    );
                    $pending = $pending->withQueryParameters($filtered);
                }

                $response = match (strtoupper($method)) {
                    'GET' => $pending->get($url),
                    'POST' => $pending->post($url, $body ?? []),
                    'PATCH' => $pending->patch($url, $body ?? []),
                    'PUT' => $pending->put($url, $body ?? []),
                    'DELETE' => $pending->delete($url, $body ?? []),
                    default => throw new \InvalidArgumentException('Unsupported HTTP method: '.$method),
                };
            } catch (ConnectionException $e) {
                $lastNetworkError = $e;
                continue;
            }

            if ($response->failed()) {
                $error = $response->json('error') ?? [];
                $message = is_array($error)
                    ? ($error['message'] ?? 'HTTP '.$response->status())
                    : 'HTTP '.$response->status();
                $code = is_array($error) ? (string) ($error['code'] ?? '') : '';

                throw new ApiException($message, $response->status(), $code);
            }

            if ($response->status() === 204 || $response->body() === '') {
                return null;
            }

            /** @var array<string, mixed>|null $json */
            $json = $response->json();

            return $json;
        }

        $reason = $lastNetworkError instanceof \Throwable
            ? $lastNetworkError->getMessage()
            : 'network error';

        $count = count($hosts);
        throw new ApiException(
            sprintf(
                'Unable to reach QattaPay API (tried %d host%s: %s)',
                $count,
                $count > 1 ? 's' : '',
                $reason
            ),
            0,
            'network_error'
        );
    }

    private function pending(): PendingRequest
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-API-Key' => $this->apiKey,
            // Harmless outside of local tunnel testing; skips ngrok's free-tier
            // HTML interstitial when baseUrl points at an *.ngrok-free.app tunnel.
            'ngrok-skip-browser-warning' => 'true',
        ])->timeout(30);
    }
}
