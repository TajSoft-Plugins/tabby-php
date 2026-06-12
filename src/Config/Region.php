<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Config;

enum Region: string
{
    case KSA = 'ksa';
    case UAE = 'uae';
    case KUWAIT = 'kuwait';

    public function baseUrl(): string
    {
        return match ($this) {
            self::KSA => 'https://api.tabby.sa',
            self::UAE, self::KUWAIT => 'https://api.tabby.ai',
        };
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim($value));

        return self::tryFrom($normalized);
    }
}
