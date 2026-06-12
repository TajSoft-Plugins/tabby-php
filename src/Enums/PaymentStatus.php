<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Enums;

enum PaymentStatus: string
{
    case Created = 'CREATED';
    case Authorized = 'AUTHORIZED';
    case Closed = 'CLOSED';
    case Rejected = 'REJECTED';
    case Expired = 'EXPIRED';

    public static function tryFromMixed(mixed $value): ?self
    {
        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        $normalized = strtoupper(trim((string) $value));

        if ($normalized === '') {
            return null;
        }

        return self::tryFrom($normalized);
    }

    public function isCapturable(): bool
    {
        return $this === self::Authorized;
    }

    public function isClosed(): bool
    {
        return $this === self::Closed;
    }

    public function isSuccessful(): bool
    {
        return in_array($this, [self::Authorized, self::Closed], true);
    }

    public function isFailed(): bool
    {
        return in_array($this, [self::Rejected, self::Expired], true);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Closed, self::Rejected, self::Expired], true);
    }
}
