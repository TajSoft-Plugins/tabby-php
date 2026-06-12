<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby;

use MustafaTaj\Tabby\Config\TabbyConfig;
use MustafaTaj\Tabby\Contracts\HttpClientInterface;

final class Tabby
{
    /**
     * @param array<string, mixed> $config
     */
    public static function make(array $config, ?HttpClientInterface $http = null): TabbyClient
    {
        return TabbyClient::fromConfig($config, $http);
    }

    public static function fromEnv(?HttpClientInterface $http = null): TabbyClient
    {
        return new TabbyClient(
            config: TabbyConfig::fromEnv(),
            http: $http ?? new Http\GuzzleHttpClient(TabbyConfig::fromEnv()),
        );
    }
}
