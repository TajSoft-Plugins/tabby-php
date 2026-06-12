<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Config;

use MustafaTaj\Tabby\Exceptions\ConfigurationException;
use MustafaTaj\Tabby\Support\BooleanParser;
use MustafaTaj\Tabby\Support\Env;

final class TabbyConfig
{
    /**
     * @param array<string, mixed> $http
     */
    public function __construct(
        private readonly string $secretKey,
        private readonly string $publicKey,
        private readonly string $merchantCode,
        private readonly string $baseUrl,
        private readonly ?Region $region,
        private readonly bool $sandbox,
        private readonly int $timeout = 30,
        private readonly array $http = [],
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function fromArray(array $config): self
    {
        $sandbox = BooleanParser::resolve($config['sandbox'] ?? false);
        [$secretKey, $publicKey] = self::resolveKeys($config, $sandbox);
        $merchantCode = self::stringValue($config['merchant_code'] ?? null);

        if ($secretKey === '') {
            throw ConfigurationException::missing('secret_key');
        }

        if ($publicKey === '') {
            throw ConfigurationException::missing('public_key');
        }

        if ($merchantCode === '') {
            throw ConfigurationException::missing('merchant_code');
        }

        $baseUrl = self::stringValue($config['base_url'] ?? null);
        $region = self::resolveRegion($config['region'] ?? null);

        if ($baseUrl === '') {
            if ($region === null) {
                throw ConfigurationException::missing('region or base_url');
            }

            $baseUrl = rtrim($region->baseUrl(), '/');
        } else {
            $baseUrl = rtrim($baseUrl, '/');
        }

        $timeout = isset($config['timeout']) ? (int) $config['timeout'] : 30;
        $http = is_array($config['http'] ?? null) ? $config['http'] : [];

        return new self(
            secretKey: $secretKey,
            publicKey: $publicKey,
            merchantCode: $merchantCode,
            baseUrl: $baseUrl,
            region: $region,
            sandbox: $sandbox,
            timeout: $timeout,
            http: $http,
        );
    }

    public static function fromEnv(): self
    {
        return self::fromArray([
            'sandbox' => Env::get('IS_TABBY_SANDBOX', 'false'),
            'keys' => [
                'live' => [
                    'secret_key' => Env::get('TABBY_LIVE_SECRET_KEY'),
                    'public_key' => Env::get('TABBY_LIVE_PUBLIC_KEY'),
                ],
                'sandbox' => [
                    'secret_key' => Env::get('TABBY_SANDBOX_SECRET_KEY'),
                    'public_key' => Env::get('TABBY_SANDBOX_PUBLIC_KEY'),
                ],
            ],
            'secret_key' => Env::get('TABBY_SECRET_KEY'),
            'public_key' => Env::get('TABBY_PUBLIC_KEY'),
            'merchant_code' => Env::get('TABBY_MERCHANT_CODE'),
            'region' => Env::get('TABBY_REGION', 'ksa'),
            'base_url' => Env::get('TABBY_BASE_URL'),
            'timeout' => Env::get('TABBY_TIMEOUT', '30'),
            'http' => [
                'connect_timeout' => Env::get('TABBY_CONNECT_TIMEOUT', '10'),
                'debug' => Env::get('TABBY_HTTP_DEBUG', 'false'),
            ],
        ]);
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getMerchantCode(): string
    {
        return $this->merchantCode;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function isSandbox(): bool
    {
        return $this->sandbox;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @return array<string, mixed>
     */
    public function getHttpOptions(): array
    {
        return $this->http;
    }

    public function getConnectTimeout(): int
    {
        $value = $this->http['connect_timeout'] ?? 10;

        return (int) $value;
    }

    public function isHttpDebugEnabled(): bool
    {
        return BooleanParser::resolve($this->http['debug'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'secret_key' => $this->secretKey,
            'public_key' => $this->publicKey,
            'merchant_code' => $this->merchantCode,
            'region' => $this->region?->value,
            'base_url' => $this->baseUrl,
            'sandbox' => $this->sandbox,
            'timeout' => $this->timeout,
            'http' => $this->http,
        ];
    }

    /**
     * @param array<string, mixed> $config
     * @return array{0: string, 1: string}
     */
    private static function resolveKeys(array $config, bool $sandbox): array
    {
        $keys = is_array($config['keys'] ?? null) ? $config['keys'] : [];
        $environment = $sandbox ? 'sandbox' : 'live';
        $environmentKeys = is_array($keys[$environment] ?? null) ? $keys[$environment] : [];

        $secretKey = self::stringValue($environmentKeys['secret_key'] ?? null);
        $publicKey = self::stringValue($environmentKeys['public_key'] ?? null);

        if ($secretKey === '') {
            $secretKey = self::stringValue($config['secret_key'] ?? null);
        }

        if ($publicKey === '') {
            $publicKey = self::stringValue($config['public_key'] ?? null);
        }

        return [$secretKey, $publicKey];
    }

    private static function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private static function resolveRegion(mixed $value): ?Region
    {
        if ($value instanceof Region) {
            return $value;
        }

        if (! is_string($value) && ! is_int($value)) {
            return null;
        }

        return Region::tryFromString((string) $value);
    }
}
