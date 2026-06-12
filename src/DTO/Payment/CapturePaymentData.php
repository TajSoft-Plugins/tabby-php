<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Payment;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class CapturePaymentData implements DataTransferObject
{
    /**
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly string $amount,
        public readonly ?string $referenceId = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = ['amount' => $this->amount];

        if ($this->referenceId !== null && $this->referenceId !== '') {
            $payload['reference_id'] = $this->referenceId;
        }

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
