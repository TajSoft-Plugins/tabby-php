<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\DTO\Checkout\CreateCheckoutSessionData;
use MustafaTaj\Tabby\Tests\Support\MockHttpClient;
use MustafaTaj\Tabby\Tests\TestCase;

final class CheckoutResourceTest extends TestCase
{
    public function test_create_sends_expected_endpoint_and_payload(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'session_1']);

        $client = $this->makeClient($http);

        $response = $client->checkout()->create([
            'payment' => [
                'amount' => '100.00',
                'currency' => 'SAR',
            ],
            'lang' => 'en',
            'merchant_urls' => [
                'success' => 'https://example.com/success',
            ],
        ]);

        $request = $http->lastRequest();

        $this->assertSame(['id' => 'session_1'], $response);
        $this->assertSame('POST', $request['method']);
        $this->assertSame('/api/v2/checkout', $request['path']);
        $this->assertSame('100.00', $request['payload']['payment']['amount']);
        $this->assertSame('en', $request['payload']['lang']);
    }

    public function test_merchant_code_is_injected_when_missing(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->checkout()->create([
            'payment' => ['amount' => '10.00', 'currency' => 'SAR'],
            'lang' => 'en',
        ]);

        $this->assertSame('merchant_code_example', $http->lastRequest()['payload']['merchant_code']);
    }

    public function test_checkout_uses_public_key_and_payments_use_secret_key(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'session_1']);
        $http->pushJsonResponse(200, ['id' => 'payment_1']);

        $client = $this->makeClient($http);
        $client->checkout()->create([
            'payment' => ['amount' => '10.00', 'currency' => 'SAR'],
            'lang' => 'en',
        ]);
        $client->payments()->retrieve('payment_1');

        $this->assertSame('Bearer pk_test_example_public_key', $http->requests[0]['headers']['Authorization']);
        $this->assertSame('Bearer sk_test_example_secret_key', $http->requests[1]['headers']['Authorization']);
    }

    public function test_auth_headers_are_attached(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->checkout()->create([
            'payment' => ['amount' => '10.00', 'currency' => 'SAR'],
            'lang' => 'en',
            'merchant_code' => 'custom_merchant',
        ]);

        $headers = $http->lastRequest()['headers'];

        $this->assertSame('Bearer pk_test_example_public_key', $headers['Authorization']);
        $this->assertSame('application/json', $headers['Accept']);
        $this->assertSame('application/json', $headers['Content-Type']);
        $this->assertArrayNotHasKey('X-Merchant-Code', $headers);
    }

    public function test_dto_payload_is_supported(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->checkout()->create(new CreateCheckoutSessionData(
            payment: ['amount' => '50.00', 'currency' => 'SAR'],
            lang: 'ar',
        ));

        $this->assertSame('ar', $http->lastRequest()['payload']['lang']);
    }

    public function test_web_url_helper_extracts_hosted_payment_page_url(): void
    {
        $session = [
            'configuration' => [
                'available_products' => [
                    'installments' => [
                        ['web_url' => 'https://checkout.tabby.ai/pay/session_1'],
                    ],
                ],
            ],
            'payment' => ['id' => 'payment_1'],
        ];

        $client = $this->makeClient(new MockHttpClient());

        $this->assertSame('https://checkout.tabby.ai/pay/session_1', $client->checkout()->webUrl($session));
        $this->assertSame('payment_1', $client->checkout()->paymentId($session));
    }
}
