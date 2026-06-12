<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class OrderItemData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly string $title,
        public readonly int $quantity,
        public readonly string $unitPrice,
        public readonly ?string $referenceId = null,
        public readonly ?string $category = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'title' => $this->title,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'reference_id' => $this->referenceId,
            'category' => $this->category,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
