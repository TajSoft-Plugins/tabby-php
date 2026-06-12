<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Config\Region;
use MustafaTaj\Tabby\Config\TabbyConfig;
use MustafaTaj\Tabby\Exceptions\ConfigurationException;
use MustafaTaj\Tabby\Tests\TestCase;

final class ConfigTest extends TestCase
{
    protected function tearDown(): void
    {
        $keys = [
            'IS_TABBY_SANDBOX',
            'TABBY_LIVE_SECRET_KEY',
            'TABBY_LIVE_PUBLIC_KEY',
            'TABBY_SANDBOX_SECRET_KEY',
            'TABBY_SANDBOX_PUBLIC_KEY',
            'TABBY_SECRET_KEY',
            'TABBY_PUBLIC_KEY',
            'TABBY_MERCHANT_CODE',
            'TABBY_REGION',
        ];

        foreach ($keys as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
        }

        parent::tearDown();
    }

    public function test_runtime_config_loading(): void
    {
        $config = TabbyConfig::fromArray($this->validConfig());

        $this->assertSame('sk_test_example_secret_key', $config->getSecretKey());
        $this->assertSame('pk_test_example_public_key', $config->getPublicKey());
        $this->assertSame('merchant_code_example', $config->getMerchantCode());
        $this->assertSame('https://api.tabby.sa', $config->getBaseUrl());
        $this->assertSame(Region::KSA, $config->getRegion());
        $this->assertSame(30, $config->getTimeout());
        $this->assertFalse($config->isSandbox());
    }

    public function test_sandbox_mode_uses_sandbox_keys(): void
    {
        $config = TabbyConfig::fromArray([
            'sandbox' => true,
            'keys' => [
                'sandbox' => [
                    'secret_key' => 'sk_test_sandbox_secret',
                    'public_key' => 'pk_test_sandbox_public',
                ],
                'live' => [
                    'secret_key' => 'sk_live_secret',
                    'public_key' => 'pk_live_public',
                ],
            ],
            'merchant_code' => 'merchant',
            'region' => 'ksa',
        ]);

        $this->assertTrue($config->isSandbox());
        $this->assertSame('sk_test_sandbox_secret', $config->getSecretKey());
        $this->assertSame('pk_test_sandbox_public', $config->getPublicKey());
    }

    public function test_live_mode_uses_live_keys(): void
    {
        $config = TabbyConfig::fromArray([
            'sandbox' => false,
            'keys' => [
                'sandbox' => [
                    'secret_key' => 'sk_test_sandbox_secret',
                    'public_key' => 'pk_test_sandbox_public',
                ],
                'live' => [
                    'secret_key' => 'sk_live_secret',
                    'public_key' => 'pk_live_public',
                ],
            ],
            'merchant_code' => 'merchant',
            'region' => 'ksa',
        ]);

        $this->assertFalse($config->isSandbox());
        $this->assertSame('sk_live_secret', $config->getSecretKey());
        $this->assertSame('pk_live_public', $config->getPublicKey());
    }

    public function test_env_config_loading_with_sandbox_keys(): void
    {
        $_ENV['IS_TABBY_SANDBOX'] = 'true';
        $_ENV['TABBY_SANDBOX_SECRET_KEY'] = 'sk_test_env_secret';
        $_ENV['TABBY_SANDBOX_PUBLIC_KEY'] = 'pk_test_env_public';
        $_ENV['TABBY_MERCHANT_CODE'] = 'env_merchant';
        $_ENV['TABBY_REGION'] = 'uae';

        $config = TabbyConfig::fromEnv();

        $this->assertTrue($config->isSandbox());
        $this->assertSame('sk_test_env_secret', $config->getSecretKey());
        $this->assertSame('pk_test_env_public', $config->getPublicKey());
        $this->assertSame('env_merchant', $config->getMerchantCode());
        $this->assertSame('https://api.tabby.ai', $config->getBaseUrl());
    }

    public function test_explicit_base_url_overrides_region(): void
    {
        $config = TabbyConfig::fromArray($this->validConfig([
            'base_url' => 'https://custom.example.com',
            'region' => Region::KSA,
        ]));

        $this->assertSame('https://custom.example.com', $config->getBaseUrl());
    }

    public function test_missing_secret_key_throws_configuration_exception(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('secret_key');

        TabbyConfig::fromArray([
            'public_key' => 'pk_test',
            'merchant_code' => 'merchant',
            'region' => 'ksa',
        ]);
    }

    public function test_missing_public_key_throws_configuration_exception(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('public_key');

        TabbyConfig::fromArray([
            'secret_key' => 'sk_test',
            'merchant_code' => 'merchant',
            'region' => 'ksa',
        ]);
    }

    public function test_missing_merchant_code_throws_configuration_exception(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('merchant_code');

        TabbyConfig::fromArray([
            'secret_key' => 'sk_test',
            'public_key' => 'pk_test',
            'region' => 'ksa',
        ]);
    }

    public function test_missing_region_and_base_url_throws_configuration_exception(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('region or base_url');

        TabbyConfig::fromArray([
            'secret_key' => 'sk_test',
            'public_key' => 'pk_test',
            'merchant_code' => 'merchant',
        ]);
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $config = TabbyConfig::fromArray($this->validConfig());

        $this->assertSame([
            'secret_key' => 'sk_test_example_secret_key',
            'public_key' => 'pk_test_example_public_key',
            'merchant_code' => 'merchant_code_example',
            'region' => 'ksa',
            'base_url' => 'https://api.tabby.sa',
            'sandbox' => false,
            'timeout' => 30,
            'http' => [],
        ], $config->toArray());
    }
}
