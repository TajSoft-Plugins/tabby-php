<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Support;

final class Sanitizer
{
    private const SENSITIVE_HEADER_KEYS = [
        'authorization',
        'x-merchant-code',
    ];

    private const SENSITIVE_PAYLOAD_KEYS = [
        'secret_key',
        'public_key',
        'token',
    ];

    /**
     * @param array<string, mixed> $headers
     * @return array<string, mixed>
     */
    public static function headers(array $headers): array
    {
        $sanitized = [];

        foreach ($headers as $key => $value) {
            $normalizedKey = strtolower((string) $key);

            if (in_array($normalizedKey, self::SENSITIVE_HEADER_KEYS, true)) {
                $sanitized[$key] = self::redact((string) $value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public static function payload(array $payload): array
    {
        return self::redactArray($payload);
    }

    public static function redact(string $value): string
    {
        $length = strlen($value);

        if ($length <= 8) {
            return '***';
        }

        return substr($value, 0, 4).'***'.substr($value, -4);
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private static function redactArray(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array(strtolower($key), self::SENSITIVE_PAYLOAD_KEYS, true)) {
                $sanitized[$key] = is_string($value) ? self::redact($value) : '***';

                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = self::redactArray($value);

                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
}
