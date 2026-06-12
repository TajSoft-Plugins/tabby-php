<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Webhooks;

use MustafaTaj\Tabby\Enums\PaymentStatus;
use MustafaTaj\Tabby\Exceptions\ValidationException;

final class WebhookPayload
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    public static function fromJson(string $json): self
    {
        $decoded = json_decode($json, true);

        if (! is_array($decoded)) {
            throw new ValidationException('Invalid Tabby webhook JSON payload.');
        }

        return self::fromArray($decoded);
    }

    /**
     * @param array<string, mixed> $headers
     */
    public static function verifyAuthHeader(
        array $headers,
        string $headerName,
        string $expectedValue,
    ): bool {
        if ($expectedValue === '') {
            return false;
        }

        $normalizedHeaders = [];

        foreach ($headers as $key => $value) {
            if (is_string($key)) {
                $normalizedHeaders[strtolower($key)] = $value;
            }
        }

        $actual = $normalizedHeaders[strtolower($headerName)] ?? null;

        if (! is_string($actual)) {
            return false;
        }

        return hash_equals($expectedValue, $actual);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    public function paymentId(): ?string
    {
        $id = $this->data['id'] ?? null;

        return is_string($id) && $id !== '' ? $id : null;
    }

    public function status(): ?PaymentStatus
    {
        return PaymentStatus::tryFromMixed($this->data['status'] ?? null);
    }

    public function amount(): ?string
    {
        $amount = $this->data['amount'] ?? null;

        if (is_string($amount) && $amount !== '') {
            return $amount;
        }

        if (is_int($amount) || is_float($amount)) {
            return (string) $amount;
        }

        return null;
    }

    public function currency(): ?string
    {
        $currency = $this->data['currency'] ?? null;

        return is_string($currency) && $currency !== '' ? $currency : null;
    }

    public function orderReferenceId(): ?string
    {
        $order = $this->data['order'] ?? null;

        if (! is_array($order)) {
            return null;
        }

        $referenceId = $order['reference_id'] ?? null;

        return is_string($referenceId) && $referenceId !== '' ? $referenceId : null;
    }

    public function isTest(): bool
    {
        return (bool) ($this->data['is_test'] ?? false);
    }

    public function isAuthorizedEvent(): bool
    {
        return $this->status() === PaymentStatus::Authorized
            && $this->captures() === [];
    }

    public function isClosedEvent(): bool
    {
        return $this->status() === PaymentStatus::Closed;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function captures(): array
    {
        $captures = $this->data['captures'] ?? [];

        return is_array($captures) ? array_values(array_filter($captures, 'is_array')) : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function refunds(): array
    {
        $refunds = $this->data['refunds'] ?? [];

        return is_array($refunds) ? array_values(array_filter($refunds, 'is_array')) : [];
    }
}
