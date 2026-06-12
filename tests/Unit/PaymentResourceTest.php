<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\DTO\Payment\CapturePaymentData;
use MustafaTaj\Tabby\DTO\Payment\ListPaymentsQuery;
use MustafaTaj\Tabby\DTO\Payment\RefundPaymentData;
use MustafaTaj\Tabby\DTO\Payment\UpdatePaymentData;
use MustafaTaj\Tabby\Tests\Support\MockHttpClient;
use MustafaTaj\Tabby\Tests\TestCase;

final class PaymentResourceTest extends TestCase
{
    public function test_retrieve_payment_uses_get_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'payment_1']);

        $client = $this->makeClient($http);
        $response = $client->payments()->retrieve('payment_1');

        $this->assertSame(['id' => 'payment_1'], $response);
        $this->assertSame('GET', $http->lastRequest()['method']);
        $this->assertSame('/api/v2/payments/payment_1', $http->lastRequest()['path']);
    }

    public function test_update_payment_uses_put_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['id' => 'payment_1']);

        $client = $this->makeClient($http);
        $client->payments()->update('payment_1', new UpdatePaymentData(referenceId: 'order-1001'));

        $this->assertSame('PUT', $http->lastRequest()['method']);
        $this->assertSame('/api/v2/payments/payment_1', $http->lastRequest()['path']);
        $this->assertSame('order-1001', $http->lastRequest()['payload']['reference_id']);
    }

    public function test_capture_payment_uses_post_captures_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['status' => 'CLOSED']);

        $client = $this->makeClient($http);
        $client->payments()->capture('payment_1', '100.00', 'capture-ref-1', ['note' => 'full capture']);

        $request = $http->lastRequest();

        $this->assertSame('POST', $request['method']);
        $this->assertSame('/api/v2/payments/payment_1/captures', $request['path']);
        $this->assertSame('100.00', $request['payload']['amount']);
        $this->assertSame('capture-ref-1', $request['payload']['reference_id']);
        $this->assertSame('full capture', $request['payload']['note']);
    }

    public function test_capture_with_data_dto(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->payments()->captureWithData('payment_1', new CapturePaymentData(amount: '25.00'));

        $this->assertSame('25.00', $http->lastRequest()['payload']['amount']);
        $this->assertArrayNotHasKey('reference_id', $http->lastRequest()['payload']);
    }

    public function test_refund_payment_uses_post_refunds_endpoint(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['status' => 'CLOSED']);

        $client = $this->makeClient($http);
        $client->payments()->refund('payment_1', '50.00', 'refund-ref-1');

        $request = $http->lastRequest();

        $this->assertSame('POST', $request['method']);
        $this->assertSame('/api/v2/payments/payment_1/refunds', $request['path']);
        $this->assertSame('50.00', $request['payload']['amount']);
        $this->assertSame('refund-ref-1', $request['payload']['reference_id']);
    }

    public function test_refund_with_data_dto(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, []);

        $client = $this->makeClient($http);
        $client->payments()->refundWithData('payment_1', new RefundPaymentData(amount: '10.00'));

        $this->assertSame('10.00', $http->lastRequest()['payload']['amount']);
    }

    public function test_list_payments_builds_query_params_correctly(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, ['payments' => []]);

        $client = $this->makeClient($http);
        $client->payments()->list(new ListPaymentsQuery(
            createdAtGte: '2024-01-01',
            createdAtLte: '2024-12-31',
            limit: 10,
            offset: 20,
        ));

        $this->assertSame([
            'created_at__gte' => '2024-01-01',
            'created_at__lte' => '2024-12-31',
            'limit' => 10,
            'offset' => 20,
        ], $http->lastRequest()['query']);
    }

    public function test_retrieve_and_capture_captures_authorized_payment(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, [
            'id' => 'payment_1',
            'status' => 'AUTHORIZED',
            'amount' => '100.00',
            'order' => ['reference_id' => 'order-1001'],
        ]);
        $http->pushJsonResponse(200, ['id' => 'capture_1', 'status' => 'CLOSED']);
        $http->pushJsonResponse(200, [
            'id' => 'payment_1',
            'status' => 'CLOSED',
            'amount' => '100.00',
        ]);

        $client = $this->makeClient($http);
        $result = $client->payments()->retrieveAndCapture('payment_1');

        $this->assertTrue($result['captured']);
        $this->assertSame('CLOSED', $result['status']);
        $this->assertSame('capture_1', $result['capture']['id']);
        $this->assertSame('CLOSED', $result['payment']['status']);
        $this->assertSame('GET', $http->requests[0]['method']);
        $this->assertSame('POST', $http->requests[1]['method']);
        $this->assertSame('/api/v2/payments/payment_1/captures', $http->requests[1]['path']);
        $this->assertSame('100.00', $http->requests[1]['payload']['amount']);
        $this->assertSame('order-1001', $http->requests[1]['payload']['reference_id']);
        $this->assertSame('GET', $http->requests[2]['method']);
    }

    public function test_retrieve_and_capture_skips_capture_for_closed_payment(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, [
            'id' => 'payment_1',
            'status' => 'CLOSED',
            'amount' => '100.00',
        ]);

        $client = $this->makeClient($http);
        $result = $client->payments()->retrieveAndCapture('payment_1');

        $this->assertFalse($result['captured']);
        $this->assertNull($result['capture']);
        $this->assertSame('CLOSED', $result['status']);
        $this->assertCount(1, $http->requests);
    }

    public function test_retrieve_and_capture_returns_unsuccessful_payment_without_capture(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, [
            'id' => 'payment_1',
            'status' => 'REJECTED',
            'amount' => '100.00',
        ]);

        $client = $this->makeClient($http);
        $result = $client->payments()->retrieveAndCapture('payment_1');

        $this->assertFalse($result['captured']);
        $this->assertNull($result['capture']);
        $this->assertSame('REJECTED', $result['status']);
        $this->assertCount(1, $http->requests);
    }

    public function test_retrieve_and_capture_uses_custom_amount_and_reference_id(): void
    {
        $http = new MockHttpClient();
        $http->pushJsonResponse(200, [
            'id' => 'payment_1',
            'status' => 'AUTHORIZED',
            'amount' => '100.00',
        ]);
        $http->pushJsonResponse(200, ['status' => 'CLOSED']);
        $http->pushJsonResponse(200, ['id' => 'payment_1', 'status' => 'CLOSED']);

        $client = $this->makeClient($http);
        $client->payments()->retrieveAndCapture(
            paymentId: 'payment_1',
            amount: '75.00',
            referenceId: 'custom-ref',
        );

        $this->assertSame('75.00', $http->requests[1]['payload']['amount']);
        $this->assertSame('custom-ref', $http->requests[1]['payload']['reference_id']);
    }
}
