<?php

namespace QattaPay\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use QattaPay\Laravel\View\Components\Button;

class QattaPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/qattapay.php', 'qattapay');

        $this->app->singleton(QattaPayClient::class, function ($app) {
            $config = $app['config']['qattapay'];

            return new QattaPayClient([
                'api_key' => (string) ($config['api_key'] ?? ''),
                'mode' => $config['mode'] ?? null,
                'base_url' => $config['base_url'] ?? null,
                'webhook_secret' => $config['webhook_secret'] ?? null,
            ]);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/qattapay.php' => config_path('qattapay.php'),
        ], 'qattapay-config');

        $this->publishes([
            __DIR__.'/../resources/js/checkout.js' => public_path('vendor/qattapay/checkout.js'),
        ], 'qattapay-assets');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'qattapay');

        Blade::component('qattapay-button', Button::class);

        $router = $this->app['router'];
        $router->aliasMiddleware(
            'qattapay.webhook',
            \QattaPay\Laravel\Http\Middleware\VerifyQattaPayWebhook::class
        );
    }
}
