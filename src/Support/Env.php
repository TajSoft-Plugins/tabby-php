<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Support;

final class Env
{
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        if (! is_string($value)) {
            return $default;
        }

        return $value;
    }
}
