<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Webhook;

use MustafaTaj\Tabby\DTO\DataTransferObject;
use MustafaTaj\Tabby\Support\Arr;

final class RegisterWebhookData implements DataTransferObject
{
    /**
     * @param array<string, string>|null $header
     * @param array<string, mixed> $extra
     */
    public function __construct(
        public readonly string $url,
        public readonly ?array $header = null,
        public readonly array $extra = [],
    ) {
    }

    public function toArray(): array
    {
        $payload = ['url' => $this->url];

        if ($this->header !== null && $this->header !== []) {
            $payload['header'] = $this->header;
        }

        return Arr::mergeRecursiveDistinct($payload, $this->extra);
    }
}
