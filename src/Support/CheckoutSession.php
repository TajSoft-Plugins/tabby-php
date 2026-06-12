<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Support;

final class CheckoutSession
{
    /**
     * @param array<string, mixed> $session
     */
    public static function webUrl(array $session, int $installmentIndex = 0): ?string
    {
        $installments = $session['configuration']['available_products']['installments'] ?? null;

        if (! is_array($installments)) {
            return null;
        }

        $product = $installments[$installmentIndex] ?? null;

        if (! is_array($product)) {
            return null;
        }

        $webUrl = $product['web_url'] ?? null;

        return is_string($webUrl) && $webUrl !== '' ? $webUrl : null;
    }

    /**
     * @param array<string, mixed> $session
     */
    public static function paymentId(array $session): ?string
    {
        $payment = $session['payment'] ?? null;

        if (! is_array($payment)) {
            return null;
        }

        $paymentId = $payment['id'] ?? null;

        return is_string($paymentId) && $paymentId !== '' ? $paymentId : null;
    }

    /**
     * @param array<string, mixed> $session
     */
    public static function sessionId(array $session): ?string
    {
        $sessionId = $session['id'] ?? null;

        return is_string($sessionId) && $sessionId !== '' ? $sessionId : null;
    }

    /**
     * @param array<string, mixed> $session
     */
    public static function status(array $session): ?string
    {
        $status = $session['status'] ?? null;

        return is_string($status) && $status !== '' ? $status : null;
    }

    /**
     * @param array<string, mixed> $session
     */
    public static function isEligible(array $session): bool
    {
        return self::status($session) === 'created' && self::webUrl($session) !== null;
    }
}
