<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Resources;

use MustafaTaj\Tabby\DTO\Payment\CapturePaymentData;
use MustafaTaj\Tabby\DTO\Payment\ListPaymentsQuery;
use MustafaTaj\Tabby\DTO\Payment\RefundPaymentData;
use MustafaTaj\Tabby\DTO\Payment\UpdatePaymentData;

final class PaymentResource extends AbstractResource
{
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
