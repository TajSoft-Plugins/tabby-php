<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Enums\PaymentStatus;
use PHPUnit\Framework\TestCase;

final class PaymentStatusTest extends TestCase
{
    public function test_try_from_mixed_is_case_insensitive(): void
    {
        $this->assertSame(PaymentStatus::Authorized, PaymentStatus::tryFromMixed('authorized'));
        $this->assertSame(PaymentStatus::Closed, PaymentStatus::tryFromMixed('CLOSED'));
    }

    public function test_is_capturable(): void
    {
        $this->assertTrue(PaymentStatus::Authorized->isCapturable());
        $this->assertFalse(PaymentStatus::Closed->isCapturable());
    }

    public function test_is_successful(): void
    {
        $this->assertTrue(PaymentStatus::Authorized->isSuccessful());
        $this->assertTrue(PaymentStatus::Closed->isSuccessful());
        $this->assertFalse(PaymentStatus::Rejected->isSuccessful());
    }

    public function test_is_failed_and_final(): void
    {
        $this->assertTrue(PaymentStatus::Rejected->isFailed());
        $this->assertTrue(PaymentStatus::Expired->isFailed());
        $this->assertTrue(PaymentStatus::Closed->isFinal());
        $this->assertFalse(PaymentStatus::Authorized->isFinal());
    }
}
