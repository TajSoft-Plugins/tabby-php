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
        unset($_ENV['TABBY_SECRET_KEY'], $_ENV['TABBY_MERCHANT_CODE'], $_ENV['TABBY_REGION']);
        unset($_SERVER['TABBY_SECRET_KEY'], $_SERVER['TABBY_MERCHANT_CODE'], $_SERVER['TABBY_REGION']);

        parent::tearDown();
    }

    public function test_runtime_config_loading(): void
    {
        $config = TabbyConfig::fromArray($this->validConfig());

        $this->assertSame('sk_test_example_secret_key', $config->getSecretKey());
        $this->assertSame('merchant_code_example', $config->getMerchantCode());
        $this->assertSame('https://api.tabby.sa', $config->getBaseUrl());
        $this->assertSame(Region::KSA, $config->getRegion());
        $this->assertSame(30, $config->getTimeout());
    }

    public function test_env_config_loading(): void
    {
        $_ENV['TABBY_SECRET_KEY'] = 'sk_test_env_key';
        $_ENV['TABBY_MERCHANT_CODE'] = 'env_merchant';
        $_ENV['TABBY_REGION'] = 'uae';

        $config = TabbyConfig::fromEnv();

        $this->assertSame('sk_test_env_key', $config->getSecretKey());
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
            'region' => 'ksa',
        ]);
    }

    public function test_missing_region_and_base_url_throws_configuration_exception(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('region or base_url');

        TabbyConfig::fromArray([
            'secret_key' => 'sk_test',
            'merchant_code' => 'merchant',
        ]);
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $config = TabbyConfig::fromArray($this->validConfig());

        $this->assertSame([
            'secret_key' => 'sk_test_example_secret_key',
            'merchant_code' => 'merchant_code_example',
            'region' => 'ksa',
            'base_url' => 'https://api.tabby.sa',
            'timeout' => 30,
            'http' => [],
        ], $config->toArray());
    }
}
