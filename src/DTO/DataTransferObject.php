<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\DTO;

interface DataTransferObject
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
