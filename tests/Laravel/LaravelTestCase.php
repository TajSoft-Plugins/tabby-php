<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Laravel;

use MustafaTaj\Tabby\Facades\Tabby;
use MustafaTaj\Tabby\Laravel\TabbyServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class LaravelTestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [TabbyServiceProvider::class];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Tabby' => Tabby::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('tabby', [
            'sandbox' => true,
            'keys' => [
                'live' => [
                    'secret_key' => 'sk_live_example_secret_key',
                    'public_key' => 'pk_live_example_public_key',
                ],
                'sandbox' => [
                    'secret_key' => 'sk_test_example_secret_key',
                    'public_key' => 'pk_test_example_public_key',
                ],
            ],
            'merchant_code' => 'merchant_code_example',
            'region' => 'ksa',
            'base_url' => null,
            'timeout' => 30,
            'http' => [
                'connect_timeout' => 10,
                'debug' => false,
            ],
        ]);
    }
}
