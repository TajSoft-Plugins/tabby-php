<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Support;

final class BooleanParser
{
    public static function resolve(mixed $value, bool $default = false): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 0;
        }

        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);

            if ($filtered !== null) {
                return $filtered;
            }

            $normalized = strtolower(trim($value));

            if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }

            return $default;
        }

        return $default;
    }
}
