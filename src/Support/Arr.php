<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Support;

final class Arr
{
    /**
     * @param array<string, mixed> $array
     * @param array<string, mixed> ...$arrays
     * @return array<string, mixed>
     */
    public static function mergeRecursiveDistinct(array $array, array ...$arrays): array
    {
        $merged = $array;

        foreach ($arrays as $current) {
            foreach ($current as $key => $value) {
                if (
                    is_array($value)
                    && isset($merged[$key])
                    && is_array($merged[$key])
                    && self::isAssociative($value)
                ) {
                    $merged[$key] = self::mergeRecursiveDistinct($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }

    /**
     * @param array<mixed> $array
     */
    public static function isAssociative(array $array): bool
    {
        if ($array === []) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }
}
