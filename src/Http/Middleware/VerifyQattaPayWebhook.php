<?php

namespace QattaPay\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use QattaPay\Laravel\Exceptions\WebhookException;
use QattaPay\Laravel\QattaPayClient;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies the `X-QattaPay-Signature` header and attaches the parsed event
 * to the request as `qattapay_event`.
 *
 * Use on a dedicated webhook route. The raw body from `$request->getContent()`
 * is verified — avoid middleware that rewrites the body before this runs.
 */
class VerifyQattaPayWebhook
{
    public function __construct(
        private readonly QattaPayClient $client,
    ) {}

    /**
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = (string) $request->header('X-QattaPay-Signature', '');
        $rawBody = $request->getContent();

        try {
            $event = $this->client->webhooks()->constructEvent($rawBody, $signature);
        } catch (WebhookException $e) {
            abort(400, $e->getMessage());
        }

        $request->attributes->set('qattapay_event', $event);

        return $next($request);
    }
}
