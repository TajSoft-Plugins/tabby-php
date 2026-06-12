<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Laravel;

use MustafaTaj\Tabby\Exceptions\ConfigurationException;
use MustafaTaj\Tabby\TabbyClient;

final class TabbyServiceProviderTest extends LaravelTestCase
{
    public function test_service_provider_registers_successfully(): void
    {
        $this->assertTrue($this->app->bound('tabby'));
        $this->assertTrue($this->app->bound(TabbyClient::class));
    }

    public function test_config_file_is_merged(): void
    {
        $this->assertTrue(config('tabby.sandbox'));
        $this->assertSame('sk_test_example_secret_key', config('tabby.keys.sandbox.secret_key'));
        $this->assertSame('pk_test_example_public_key', config('tabby.keys.sandbox.public_key'));
        $this->assertSame('merchant_code_example', config('tabby.merchant_code'));
        $this->assertSame('ksa', config('tabby.region'));
        $this->assertSame(30, config('tabby.timeout'));
    }

    public function test_tabby_client_can_be_injected_from_container(): void
    {
        $client = $this->app->make(TabbyClient::class);

        $this->assertInstanceOf(TabbyClient::class, $client);
        $this->assertTrue($client->getConfig()->isSandbox());
        $this->assertSame('sk_test_example_secret_key', $client->getConfig()->getSecretKey());
        $this->assertSame('pk_test_example_public_key', $client->getConfig()->getPublicKey());
        $this->assertSame('merchant_code_example', $client->getConfig()->getMerchantCode());
    }

    public function test_singleton_binding_returns_same_instance(): void
    {
        $first = $this->app->make('tabby');
        $second = $this->app->make(TabbyClient::class);

        $this->assertSame($first, $second);
    }

    public function test_missing_env_values_do_not_break_package_discovery(): void
    {
        $this->app['config']->set('tabby', [
            'sandbox' => true,
            'keys' => [
                'live' => ['secret_key' => null, 'public_key' => null],
                'sandbox' => ['secret_key' => null, 'public_key' => null],
            ],
            'merchant_code' => null,
            'region' => 'ksa',
            'timeout' => 30,
            'http' => [],
        ]);

        $this->assertTrue($this->app->bound('tabby'));
    }

    public function test_client_throws_config_exception_only_when_resolved_with_missing_values(): void
    {
        $this->app['config']->set('tabby', [
            'sandbox' => true,
            'keys' => [
                'live' => ['secret_key' => null, 'public_key' => null],
                'sandbox' => ['secret_key' => null, 'public_key' => null],
            ],
            'merchant_code' => null,
            'region' => 'ksa',
            'timeout' => 30,
            'http' => [],
        ]);

        $this->expectException(ConfigurationException::class);

        $this->app->make('tabby');
    }
}
