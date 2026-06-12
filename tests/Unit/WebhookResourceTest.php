<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\DTO\Webhook\RegisterWebhookData;
use MustafaTaj\Tabby\DTO\Webhook\UpdateWebhookData;
use MustafaTaj\Tabby\Tests\Support\MockHttpClient;
use MustafaTaj\Tabby\Tests\TestCase;

final class WebhookResourceTest extends TestCase
{
    public function test_register_webhook_uses_post_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'wh_1']);

        $client = $this->makeClient($http);
        $response = $client->webhooks()->register(
            'https://example.com/webhooks/tabby',
            ['title' => 'X-Webhook-Secret', 'value' => 'secret'],
        );

        $request = $http->lastRequest();

        $this->assertSame(['id' => 'wh_1'], $response);
        $this->assertSame('POST', $request['method']);
        $this->assertSame('/api/v1/webhooks', $request['path']);
        $this->assertSame('https://example.com/webhooks/tabby', $request['payload']['url']);
        $this->assertSame('secret', $request['payload']['header']['value']);
    }

    public function test_webhook_requests_include_x_merchant_code_header(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->webhooks()->registerWithData(new RegisterWebhookData(url: 'https://example.com/hook'));

        $headers = $http->lastRequest()['headers'];

        $this->assertSame('merchant_code_example', $headers['X-Merchant-Code']);
        $this->assertSame('Bearer sk_test_example_secret_key', $headers['Authorization']);
    }

    public function test_all_webhooks_uses_get_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['webhooks' => []]);

        $client = $this->makeClient($http);
        $client->webhooks()->all();

        $this->assertSame('GET', $http->lastRequest()['method']);
        $this->assertSame('/api/v1/webhooks', $http->lastRequest()['path']);
    }

    public function test_retrieve_webhook_uses_get_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'wh_1']);

        $client = $this->makeClient($http);
        $client->webhooks()->retrieve('wh_1');

        $this->assertSame('GET', $http->lastRequest()['method']);
        $this->assertSame('/api/v1/webhooks/wh_1', $http->lastRequest()['path']);
    }

    public function test_update_webhook_uses_put_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'wh_1']);

        $client = $this->makeClient($http);
        $client->webhooks()->update('wh_1', new UpdateWebhookData(url: 'https://example.com/new'));

        $this->assertSame('PUT', $http->lastRequest()['method']);
        $this->assertSame('/api/v1/webhooks/wh_1', $http->lastRequest()['path']);
        $this->assertSame('https://example.com/new', $http->lastRequest()['payload']['url']);
    }

    public function test_delete_webhook_uses_delete_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushResponse(new \MustafaTaj\Tabby\Http\Response(200, ''));

        $client = $this->makeClient($http);
        $result = $client->webhooks()->delete('wh_1');

        $this->assertSame('DELETE', $http->lastRequest()['method']);
        $this->assertSame('/api/v1/webhooks/wh_1', $http->lastRequest()['path']);
        $this->assertTrue($result);
    }
}
