<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO\Payment;

use MustafaTaj\Tabby\DTO\DataTransferObject;

final class ListPaymentsQuery implements DataTransferObject
{
    public function __construct(
        public readonly ?string $createdAtGte = null,
        public readonly ?string $createdAtLte = null,
        public readonly ?int $limit = null,
        public readonly ?int $offset = null,
    ) {
    }

    public function toArray(): array
    {
        $query = [];

        if ($this->createdAtGte !== null && $this->createdAtGte !== '') {
            $query['created_at__gte'] = $this->createdAtGte;
        }

        if ($this->createdAtLte !== null && $this->createdAtLte !== '') {
            $query['created_at__lte'] = $this->createdAtLte;
        }

        if ($this->limit !== null) {
            $query['limit'] = $this->limit;
        }

        if ($this->offset !== null) {
            $query['offset'] = $this->offset;
        }

        return $query;
    }
}
