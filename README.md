# Tabby PHP SDK

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mustafataj/tabby-php.svg?style=flat-square)](https://packagist.org/packages/mustafataj/tabby-php)
[![Total Downloads](https://img.shields.io/packagist/dt/mustafataj/tabby-php.svg?style=flat-square)](https://packagist.org/packages/mustafataj/tabby-php)
[![License](https://img.shields.io/packagist/l/mustafataj/tabby-php.svg?style=flat-square)](LICENSE)

Production-ready PHP SDK for [Tabby Pay in 4 Custom API](https://docs.tabby.ai/pay-in-4-custom-integration/quick-start). Works in plain PHP and Laravel 10+ out of the box.

## Introduction

This package provides a framework-agnostic core with first-class Laravel integration for:

- Creating checkout sessions
- Retrieving and updating payments
- Capturing and refunding payments
- Listing payments
- Managing webhooks

The SDK uses Guzzle by default and supports injecting a custom HTTP client through `HttpClientInterface`.

### API keys

Tabby uses two credentials:

| Key | Used for |
|-----|----------|
| **Public key** (`pk_test_...` / `pk_...`) | Checkout session creation |
| **Secret key** (`sk_test_...` / `sk_...`) | Payments and webhooks |

The SDK selects the correct key automatically per endpoint. Use sandbox keys while testing and live keys in production.

## Installation

```bash
composer require mustafataj/tabby-php
```

## Laravel Setup

The package auto-registers via Laravel package discovery:

- Service provider: `MustafaTaj\Tabby\Laravel\TabbyServiceProvider`
- Facade alias: `Tabby`

No manual registration is required for Laravel 10, 11, or 12.

## Publishing Config

```bash
php artisan vendor:publish --tag=tabby-config
```

This publishes `config/tabby.php` to your application.

## Environment Variables

Add the following to your `.env`:

```dotenv
IS_TABBY_SANDBOX=true

TABBY_LIVE_SECRET_KEY=sk_xxx
TABBY_LIVE_PUBLIC_KEY=pk_xxx
TABBY_SANDBOX_SECRET_KEY=sk_test_xxx
TABBY_SANDBOX_PUBLIC_KEY=pk_test_xxx

TABBY_MERCHANT_CODE=your_merchant_code
TABBY_REGION=ksa
TABBY_BASE_URL=
TABBY_TIMEOUT=30
TABBY_CONNECT_TIMEOUT=10
TABBY_HTTP_DEBUG=false
```

When `IS_TABBY_SANDBOX=true`, the SDK uses `TABBY_SANDBOX_*` keys. When `false`, it uses `TABBY_LIVE_*` keys.

Optional legacy overrides (used only if the active environment keys are empty):

```dotenv
TABBY_SECRET_KEY=
TABBY_PUBLIC_KEY=
```

## Plain PHP Setup

```php
<?php

require __DIR__.'/vendor/autoload.php';

use MustafaTaj\Tabby\Config\Region;
use MustafaTaj\Tabby\Tabby;

$tabby = Tabby::make([
    'sandbox' => true,
    'keys' => [
        'sandbox' => [
            'secret_key' => 'sk_test_xxx',
            'public_key' => 'pk_test_xxx',
        ],
        'live' => [
            'secret_key' => 'sk_xxx',
            'public_key' => 'pk_xxx',
        ],
    ],
    'merchant_code' => 'your_merchant_code',
    'region' => Region::KSA,
]);

$session = $tabby->checkout()->create([...]); // uses public key
$payment = $tabby->payments()->retrieve('payment_id_here'); // uses secret key
```

Or pass keys directly without the nested structure:

```php
$tabby = Tabby::make([
    'secret_key' => 'sk_test_xxx',
    'public_key' => 'pk_test_xxx',
    'merchant_code' => 'your_merchant_code',
    'region' => Region::KSA,
]);
```

You can also load configuration from environment variables:

```php
$tabby = Tabby::fromEnv();
```

## Region and Base URL

| Region   | Value     | Base URL               |
|----------|-----------|------------------------|
| KSA      | `ksa`     | `https://api.tabby.sa` |
| UAE      | `uae`     | `https://api.tabby.ai` |
| Kuwait   | `kuwait`  | `https://api.tabby.ai` |

If `base_url` is explicitly configured, it overrides the region mapping.

## Checkout Session Example

```php
use MustafaTaj\Tabby\Facades\Tabby;

$session = Tabby::checkout()->create([
    'payment' => [
        'amount' => '100.00',
        'currency' => 'SAR',
        'description' => 'Order #1001',
        'buyer' => [
            'phone' => '500000001',
            'email' => 'otp.success@tabby.ai',
            'name' => 'Test Customer',
        ],
        'order' => [
            'reference_id' => '1001',
            'items' => [
                [
                    'title' => 'Product name',
                    'quantity' => 1,
                    'unit_price' => '100.00',
                    'reference_id' => 'SKU-001',
                ],
            ],
        ],
    ],
    'lang' => 'en',
    'merchant_urls' => [
        'success' => route('checkout.success'),
        'cancel' => route('checkout.cancel'),
        'failure' => route('checkout.failure'),
    ],
]);
```

If `merchant_code` is omitted from the payload, it is injected from config automatically.

## Redirect to Hosted Payment Page

```php
$webUrl = Tabby::checkout()->webUrl($session);

if ($webUrl) {
    return redirect()->away($webUrl);
}

// Or use the helper directly:
use MustafaTaj\Tabby\Support\CheckoutSession;

$webUrl = CheckoutSession::webUrl($session);
$paymentId = CheckoutSession::paymentId($session);
```

## Retrieve Payment Example

```php
use MustafaTaj\Tabby\Facades\Tabby;

$payment = Tabby::payments()->retrieve($paymentId);
```

### Dependency Injection

```php
use MustafaTaj\Tabby\TabbyClient;

class PaymentController
{
    public function show(TabbyClient $tabby, string $paymentId)
    {
        return response()->json(
            $tabby->payments()->retrieve($paymentId)
        );
    }
}
```

## Success Payment Callback Example

After the customer returns from Tabby's hosted payment page, verify the payment and capture it in one call:

```php
use MustafaTaj\Tabby\Facades\Tabby;

$result = Tabby::payments()->retrieveAndCapture(
    paymentId: $paymentId,
    referenceId: 'capture-order-1001',
);

if ($result['successful']) {
    // Fulfill the order
}

// $result shape:
// [
//     'payment' => [...],    // latest payment object from Tabby
//     'captured' => true,    // true when a capture request was sent in this call
//     'capture' => [...],    // capture response, or null when not captured
//     'status' => 'CLOSED',
//     'successful' => true,  // true for AUTHORIZED or CLOSED payments
// ]
```

`retrieveAndCapture()` retrieves the payment first. If the status is `AUTHORIZED`, it captures the full payment amount (or a custom amount you pass). If the payment is already `CLOSED`, it returns the payment without sending another capture request.

## Close Payment Example

Use this when an order is fully cancelled and should not be captured:

```php
Tabby::payments()->close($paymentId);
```

## Payment Status Helper

```php
use MustafaTaj\Tabby\Enums\PaymentStatus;

$status = PaymentStatus::tryFromMixed($payment['status']);

if ($status?->isCapturable()) {
    Tabby::payments()->capture($paymentId, $payment['amount']);
}

if ($status?->isSuccessful()) {
    // Payment is authorized or closed
}
```

## Capture Payment Example

```php
Tabby::payments()->capture(
    paymentId: $paymentId,
    amount: '100.00',
    referenceId: 'capture-order-1001'
);
```

## Refund Payment Example

```php
Tabby::payments()->refund(
    paymentId: $paymentId,
    amount: '50.00',
    referenceId: 'refund-order-1001-1'
);
```

## List Payments Example

```php
use MustafaTaj\Tabby\DTO\Payment\ListPaymentsQuery;

$payments = Tabby::payments()->list(new ListPaymentsQuery(
    createdAtGte: '2024-01-01T00:00:00Z',
    createdAtLte: '2024-12-31T23:59:59Z',
    limit: 20,
    offset: 0,
));

// Or with a raw array:
$payments = Tabby::payments()->list([
    'created_at__gte' => '2024-01-01T00:00:00Z',
    'limit' => 20,
]);
```

## Update Payment Example

```php
Tabby::payments()->update($paymentId, [
    'reference_id' => 'updated-order-reference',
]);
```

## Webhook Registration Example

```php
Tabby::webhooks()->register(
    url: 'https://example.com/webhooks/tabby',
    header: [
        'title' => 'X-Webhook-Secret',
        'value' => 'my-secret',
    ]
);
```

Webhook requests automatically include the `X-Merchant-Code` header from config.

## Webhook CRUD Examples

```php
// List all webhooks
$webhooks = Tabby::webhooks()->all();

// Retrieve a webhook
$webhook = Tabby::webhooks()->retrieve($webhookId);

// Update a webhook
$updated = Tabby::webhooks()->update($webhookId, [
    'url' => 'https://example.com/webhooks/tabby-v2',
]);

// Delete a webhook
Tabby::webhooks()->delete($webhookId);
```

## Incoming Webhook Payload Parser

Parse and inspect Tabby webhook POST bodies in your Laravel controller or plain PHP handler:

```php
use MustafaTaj\Tabby\Webhooks\WebhookPayload;

$payload = WebhookPayload::fromJson($request->getContent());

if (! WebhookPayload::verifyAuthHeader(
    headers: $request->headers->all(),
    headerName: 'X-Webhook-Secret',
    expectedValue: config('services.tabby.webhook_secret'),
)) {
    abort(401);
}

if ($payload->isAuthorizedEvent()) {
    Tabby::payments()->capture(
        paymentId: $payload->paymentId(),
        amount: $payload->amount(),
        referenceId: $payload->orderReferenceId(),
    );
}

if ($payload->isClosedEvent()) {
    // Payment completed — no action required
}
```

Webhook payloads use lowercase statuses (`authorized`, `closed`). The parser normalizes them via `PaymentStatus`.

## Error Handling

```php
use MustafaTaj\Tabby\Exceptions\ApiException;
use MustafaTaj\Tabby\Exceptions\AuthenticationException;
use MustafaTaj\Tabby\Exceptions\ConfigurationException;
use MustafaTaj\Tabby\Exceptions\NetworkException;
use MustafaTaj\Tabby\Exceptions\ValidationException;

try {
    $payment = Tabby::payments()->retrieve($paymentId);
} catch (AuthenticationException $e) {
    // HTTP 401 / 403
} catch (ValidationException $e) {
    // HTTP 400 / 422
} catch (ApiException $e) {
    // Other non-success API responses
    $status = $e->getStatusCode();
    $body = $e->getResponseJson();
} catch (NetworkException $e) {
    // Connection errors and timeouts
} catch (ConfigurationException $e) {
    // Missing or invalid SDK configuration
}
```

Exception objects expose sanitized request context and never include raw secret keys.

## Optional DTOs

All resource methods accept plain arrays. DTOs are optional helpers:

```php
use MustafaTaj\Tabby\DTO\Payment\CapturePaymentData;

Tabby::payments()->captureWithData(
    paymentId: $paymentId,
    data: new CapturePaymentData(
        amount: '100.00',
        referenceId: 'capture-1001',
    ),
);
```

## Custom HTTP Client

Implement `MustafaTaj\Tabby\Contracts\HttpClientInterface` and pass it to `Tabby::make()`:

```php
$tabby = Tabby::make($config, $customHttpClient);
```

## Testing

```bash
composer validate
composer dump-autoload
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

The test suite uses mocked HTTP clients and does not make real Tabby API calls.

GitHub Actions runs the same checks on PHP 8.1 through 8.4 for every push and pull request to `main`.

## Security Notes

- Never commit real Tabby public or secret keys to source control.
- Use sandbox/test credentials during development (`IS_TABBY_SANDBOX=true`).
- Store secrets in `.env` or a secure secret manager.
- Do not expose secret keys to frontend clients. Public keys are intended for checkout session creation only.
- Validate incoming webhook requests in your application according to your security rules and any Tabby-provided headers or secrets.

## Contributing

Contributions are welcome. Please open an issue or pull request on [GitHub](https://github.com/TajSoft-Plugins/tabby-php).

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).
