<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Support\CheckoutSession;
use PHPUnit\Framework\TestCase;

final class CheckoutSessionTest extends TestCase
{
    public function test_web_url_is_extracted_from_checkout_session(): void
    {
        $session = [
            'id' => 'session_1',
            'status' => 'created',
            'configuration' => [
                'available_products' => [
                    'installments' => [
                        ['web_url' => 'https://checkout.tabby.ai/pay/session_1'],
                    ],
                ],
            ],
        ];

        $this->assertSame('https://checkout.tabby.ai/pay/session_1', CheckoutSession::webUrl($session));
        $this->assertSame('session_1', CheckoutSession::sessionId($session));
        $this->assertSame('created', CheckoutSession::status($session));
        $this->assertTrue(CheckoutSession::isEligible($session));
    }

    public function test_payment_id_is_extracted_from_checkout_session(): void
    {
        $session = [
            'payment' => ['id' => 'payment_123'],
        ];

        $this->assertSame('payment_123', CheckoutSession::paymentId($session));
    }

    public function test_web_url_returns_null_when_missing(): void
    {
        $this->assertNull(CheckoutSession::webUrl([]));
        $this->assertFalse(CheckoutSession::isEligible([]));
    }
}
