<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class PaymentData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $buyer
     * @param array<string, mixed> $buyerHistory
     * @param array<string, mixed> $order
     * @param array<string, mixed> $orderHistory
     * @param array<string, mixed> $shippingAddress
     * @param array<string, mixed> $meta
     * @param array<string, mixed> $attachment
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly string $amount,
        public readonly string $currency,
        public readonly ?string $description = null,
        public readonly array $buyer = [],
        public readonly array $buyerHistory = [],
        public readonly array $order = [],
        public readonly array $orderHistory = [],
        public readonly array $shippingAddress = [],
        public readonly array $meta = [],
        public readonly array $attachment = [],
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'amount' => $this->amount,
            'currency' => $this->currency,
            'description' => $this->description,
            'buyer' => $this->buyer !== [] ? $this->buyer : null,
            'buyer_history' => $this->buyerHistory !== [] ? $this->buyerHistory : null,
            'order' => $this->order !== [] ? $this->order : null,
            'order_history' => $this->orderHistory !== [] ? $this->orderHistory : null,
            'shipping_address' => $this->shippingAddress !== [] ? $this->shippingAddress : null,
            'meta' => $this->meta !== [] ? $this->meta : null,
            'attachment' => $this->attachment !== [] ? $this->attachment : null,
        ], static fn (mixed $value): bool => $value !== null);

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
