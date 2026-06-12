<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class CreateCheckoutSessionData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $payment
     * @param array<string, mixed> $merchantUrls
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly array $payment,
        public readonly string $lang = 'en',
        public readonly ?string $merchantCode = null,
        public readonly array $merchantUrls = [],
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = [
            'payment' => $this->payment,
            'lang' => $this->lang,
            'merchant_urls' => $this->merchantUrls,
        ];

        if ($this->merchantCode !== null && $this->merchantCode !== '') {
            $payload['merchant_code'] = $this->merchantCode;
        }

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
