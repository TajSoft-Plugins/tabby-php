<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Facades;

use Illuminate\Support\Facades\Facade;
use MustafaTaj\Tabby\Resources\CheckoutResource;
use MustafaTaj\Tabby\Resources\PaymentResource;
use MustafaTaj\Tabby\Resources\WebhookResource;
use MustafaTaj\Tabby\TabbyClient;

/**
 * @method static CheckoutResource checkout()
 * @method static PaymentResource payments()
 * @method static WebhookResource webhooks()
 *
 * @see TabbyClient
 */
final class Tabby extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tabby';
    }
}
