<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests;

use MustafaTaj\Tabby\Config\Region;
use MustafaTaj\Tabby\Tabby;
use MustafaTaj\Tabby\TabbyClient;
use MustafaTaj\Tabby\Tests\Support\MockHttpClient;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function validConfig(array $overrides = []): array
    {
        return array_merge([
            'secret_key' => 'sk_test_example_secret_key',
            'merchant_code' => 'merchant_code_example',
            'region' => Region::KSA,
            'timeout' => 30,
        ], $overrides);
    }

    protected function makeClient(?MockHttpClient $http = null, array $config = []): TabbyClient
    {
        return Tabby::make(
            $this->validConfig($config),
            $http ?? new MockHttpClient(),
        );
    }
}
