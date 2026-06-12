<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Exceptions\ApiException;
use MustafaTaj\Tabby\Http\Response;
use MustafaTaj\Tabby\Tests\Support\MockHttpClient;
use MustafaTaj\Tabby\Tests\TestCase;

final class ClientTest extends TestCase
{
    public function test_client_exposes_resources(): void
    {
        $client = $this->makeClient();

        $this->assertSame($client->checkout(), $client->checkout());
        $this->assertSame($client->payments(), $client->payments());
        $this->assertSame($client->webhooks(), $client->webhooks());
    }

    public function test_get_config_returns_tabby_config(): void
    {
        $client = $this->makeClient();

        $this->assertSame('sk_test_example_secret_key', $client->getConfig()->getSecretKey());
    }

    public function test_response_json_decoding(): void
    {
        $response = new Response(200, '{"id":"payment_1"}');

        $this->assertTrue($response->successful());
        $this->assertSame(['id' => 'payment_1'], $response->json());
    }

    public function test_mock_http_client_records_requests(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['ok' => true]);

        $http->get('/api/v2/payments/1', [], ['Accept' => 'application/json']);

        $this->assertSame('GET', $http->lastRequest()['method']);
        $this->assertSame('/api/v2/payments/1', $http->lastRequest()['path']);
    }

    public function test_non_success_response_throws_from_resources(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(500, ['error' => 'Server error']);

        $client = $this->makeClient($http);

        $this->expectException(ApiException::class);

        $client->payments()->retrieve('payment_1');
    }
}
