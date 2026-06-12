<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class BuyerData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?string $name = null,
        public readonly ?string $dob = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'phone' => $this->phone,
            'email' => $this->email,
            'name' => $this->name,
            'dob' => $this->dob,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
