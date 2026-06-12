<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Resources;

use MustafaTaj\Tabby\DTO\Payment\CapturePaymentData;
use MustafaTaj\Tabby\DTO\Payment\ListPaymentsQuery;
use MustafaTaj\Tabby\DTO\Payment\RefundPaymentData;
use MustafaTaj\Tabby\DTO\Payment\UpdatePaymentData;
use MustafaTaj\Tabby\Exceptions\ValidationException;

final class PaymentResource extends AbstractResource
{
    private const CAPTURABLE_STATUSES = ['AUTHORIZED'];

    private const COMPLETED_STATUSES = ['CLOSED'];
    /**
     * @return array<string, mixed>
     */
    public function retrieve(string $paymentId): array
    {
        $response = $this->http->get(
            sprintf('/api/v2/payments/%s', rawurlencode($paymentId)),
            [],
            $this->secretHeaders(),
        );

        return $this->decode($response);
    }

    /**
     * Retrieve a payment and capture it when authorized (success callback flow).
     *
     * @param array<string, mixed> $extra
     * @return array{
     *     payment: array<string, mixed>,
     *     captured: bool,
     *     capture: array<string, mixed>|null,
     *     status: string
     * }
     */
    public function retrieveAndCapture(
        string $paymentId,
        ?string $amount = null,
        ?string $referenceId = null,
        array $extra = [],
    ): array {
        $payment = $this->retrieve($paymentId);
        $status = strtoupper((string) ($payment['status'] ?? ''));

        if (in_array($status, self::COMPLETED_STATUSES, true)) {
            return [
                'payment' => $payment,
                'captured' => false,
                'capture' => null,
                'status' => $status,
            ];
        }

        if (! in_array($status, self::CAPTURABLE_STATUSES, true)) {
            return [
                'payment' => $payment,
                'captured' => false,
                'capture' => null,
                'status' => $status,
            ];
        }

        $capture = $this->capture(
            paymentId: $paymentId,
            amount: $this->resolveCaptureAmount($payment, $amount),
            referenceId: $referenceId ?? $this->resolveCaptureReferenceId($payment),
            extra: $extra,
        );

        return [
            'payment' => $this->retrieve($paymentId),
            'captured' => true,
            'capture' => $capture,
            'status' => strtoupper((string) ($capture['status'] ?? $status)),
        ];
    }

    /**
     * @param array<string, mixed> $payment
     */
    private function resolveCaptureAmount(array $payment, ?string $amount): string
    {
        if ($amount !== null && $amount !== '') {
            return $amount;
        }

        $paymentAmount = $payment['amount'] ?? null;

        if (is_string($paymentAmount) && $paymentAmount !== '') {
            return $paymentAmount;
        }

        if (is_int($paymentAmount) || is_float($paymentAmount)) {
            return (string) $paymentAmount;
        }

        throw new ValidationException('Unable to determine capture amount for authorized payment.');
    }

    /**
     * @param array<string, mixed> $payment
     */
    private function resolveCaptureReferenceId(array $payment): ?string
    {
        $order = $payment['order'] ?? null;

        if (! is_array($order)) {
            return null;
        }

        $referenceId = $order['reference_id'] ?? null;

        if (! is_string($referenceId) || $referenceId === '') {
            return null;
        }

        return $referenceId;
    }

    /**
     * @param array<string, mixed>|UpdatePaymentData $payload
     * @return array<string, mixed>
     */
    public function update(string $paymentId, array|UpdatePaymentData $payload): array
    {
        $data = $payload instanceof UpdatePaymentData ? $payload->toArray() : $payload;

        $response = $this->http->put(
            sprintf('/api/v2/payments/%s', rawurlencode($paymentId)),
            $data,
            $this->secretHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    public function capture(
        string $paymentId,
        string $amount,
        ?string $referenceId = null,
        array $extra = [],
    ): array {
        return $this->captureWithData(
            $paymentId,
            new CapturePaymentData(
                amount: $amount,
                referenceId: $referenceId,
                extra: $extra,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function captureWithData(string $paymentId, CapturePaymentData $data): array
    {
        $response = $this->http->post(
            sprintf('/api/v2/payments/%s/captures', rawurlencode($paymentId)),
            $data->toArray(),
            $this->secretHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    public function refund(
        string $paymentId,
        string $amount,
        ?string $referenceId = null,
        array $extra = [],
    ): array {
        return $this->refundWithData(
            $paymentId,
            new RefundPaymentData(
                amount: $amount,
                referenceId: $referenceId,
                extra: $extra,
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function refundWithData(string $paymentId, RefundPaymentData $data): array
    {
        $response = $this->http->post(
            sprintf('/api/v2/payments/%s/refunds', rawurlencode($paymentId)),
            $data->toArray(),
            $this->secretHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed>|ListPaymentsQuery $query
     * @return array<string, mixed>
     */
    public function list(array|ListPaymentsQuery $query = []): array
    {
        $params = $query instanceof ListPaymentsQuery ? $query->toArray() : $query;

        $response = $this->http->get(
            '/api/v2/payments',
            $params,
            $this->secretHeaders(),
        );

        return $this->decode($response);
    }
}
