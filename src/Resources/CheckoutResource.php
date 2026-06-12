<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Resources;

use MustafaTaj\Tabby\DTO\Checkout\CreateCheckoutSessionData;
use MustafaTaj\Tabby\Support\CheckoutSession;

final class CheckoutResource extends AbstractResource
{
    /**
     * @param array<string, mixed>|CreateCheckoutSessionData $payload
     * @return array<string, mixed>
     */
    public function create(array|CreateCheckoutSessionData $payload): array
    {
        $data = $this->normalizePayload($payload);

        if (! isset($data['merchant_code']) || $data['merchant_code'] === '') {
            $data['merchant_code'] = $this->config->getMerchantCode();
        }

        $response = $this->http->post(
            '/api/v2/checkout',
            $data,
            $this->checkoutHeaders(true),
        );

        return $this->decode($response);
    }

    /**
     * @param array<string, mixed> $session
     */
    public function webUrl(array $session, int $installmentIndex = 0): ?string
    {
        return CheckoutSession::webUrl($session, $installmentIndex);
    }

    /**
     * @param array<string, mixed> $session
     */
    public function paymentId(array $session): ?string
    {
        return CheckoutSession::paymentId($session);
    }

    /**
     * @param array<string, mixed>|CreateCheckoutSessionData $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array|CreateCheckoutSessionData $payload): array
    {
        if ($payload instanceof CreateCheckoutSessionData) {
            return $payload->toArray();
        }

        return $payload;
    }
}
