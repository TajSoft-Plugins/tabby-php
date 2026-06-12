<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class ShippingAddressData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly ?string $city = null,
        public readonly ?string $address = null,
        public readonly ?string $zip = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'city' => $this->city,
            'address' => $this->address,
            'zip' => $this->zip,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
