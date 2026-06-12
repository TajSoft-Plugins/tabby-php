<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Checkout;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class MerchantUrlsData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly ?string $success = null,
        public readonly ?string $cancel = null,
        public readonly ?string $failure = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = array_filter([
            'success' => $this->success,
            'cancel' => $this->cancel,
            'failure' => $this->failure,
        ], static fn (mixed $value): bool => $value !== null && $value !== '');

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
