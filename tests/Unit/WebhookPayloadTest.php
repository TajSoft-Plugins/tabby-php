<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Enums\PaymentStatus;
use MustafaTaj\Tabby\Exceptions\ValidationException;
use MustafaTaj\Tabby\Webhooks\WebhookPayload;
use PHPUnit\Framework\TestCase;

final class WebhookPayloadTest extends TestCase
{
    public function test_from_array_parses_payment_fields(): void
    {
        $payload = WebhookPayload::fromArray([
            'id' => 'payment_1',
            'status' => 'authorized',
            'amount' => '100.00',
            'currency' => 'SAR',
            'is_test' => true,
            'order' => ['reference_id' => 'order-1001'],
            'captures' => [],
            'refunds' => [],
        ]);

        $this->assertSame('payment_1', $payload->paymentId());
        $this->assertSame(PaymentStatus::Authorized, $payload->status());
        $this->assertSame('100.00', $payload->amount());
        $this->assertSame('SAR', $payload->currency());
        $this->assertSame('order-1001', $payload->orderReferenceId());
        $this->assertTrue($payload->isTest());
        $this->assertTrue($payload->isAuthorizedEvent());
        $this->assertFalse($payload->isClosedEvent());
    }

    public function test_from_json_parses_payload(): void
    {
        $payload = WebhookPayload::fromJson('{"id":"payment_1","status":"closed"}');

        $this->assertSame('payment_1', $payload->paymentId());
        $this->assertSame(PaymentStatus::Closed, $payload->status());
        $this->assertTrue($payload->isClosedEvent());
    }

    public function test_from_json_throws_for_invalid_payload(): void
    {
        $this->expectException(ValidationException::class);

        WebhookPayload::fromJson('not-json');
    }

    public function test_verify_auth_header(): void
    {
        $this->assertTrue(WebhookPayload::verifyAuthHeader(
            ['X-Webhook-Secret' => 'my-secret'],
            'X-Webhook-Secret',
            'my-secret',
        ));

        $this->assertFalse(WebhookPayload::verifyAuthHeader(
            ['X-Webhook-Secret' => 'wrong'],
            'X-Webhook-Secret',
            'my-secret',
        ));
    }

    public function test_capture_event_is_not_treated_as_authorization_event(): void
    {
        $payload = WebhookPayload::fromArray([
            'id' => 'payment_1',
            'status' => 'authorized',
            'captures' => [
                ['id' => 'capture_1', 'amount' => '100.00'],
            ],
        ]);

        $this->assertFalse($payload->isAuthorizedEvent());
        $this->assertCount(1, $payload->captures());
    }
}
