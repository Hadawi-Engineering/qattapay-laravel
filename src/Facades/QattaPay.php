<?php

namespace QattaPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use QattaPay\Laravel\QattaPayClient;
use QattaPay\Laravel\Resources\Intents;
use QattaPay\Laravel\Resources\Orders;
use QattaPay\Laravel\Webhooks\Webhooks;

/**
 * @method static Intents intents()
 * @method static Orders orders()
 * @method static Webhooks webhooks()
 *
 * @see QattaPayClient
 */
class QattaPay extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return QattaPayClient::class;
    }
}
