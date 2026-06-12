<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class OrderData implements DataTransferObject
{
    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly ?string $referenceId = null,
        public readonly ?string $taxAmount = null,
        public readonly ?string $shippingAmount = null,
        public readonly ?string $discountAmount = null,
        public readonly array $items = [],
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'reference_id' => $this->referenceId,
            'tax_amount' => $this->taxAmount,
            'shipping_amount' => $this->shippingAmount,
            'discount_amount' => $this->discountAmount,
            'items' => $this->items !== [] ? $this->items : null,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
