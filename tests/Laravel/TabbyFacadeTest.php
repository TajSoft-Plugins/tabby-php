<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Laravel;

use MustafaTaj\Tabby\Facades\Tabby;
use MustafaTaj\Tabby\TabbyClient;

final class TabbyFacadeTest extends LaravelTestCase
{
    public function test_facade_resolves_correctly(): void
    {
        $this->assertInstanceOf(TabbyClient::class, Tabby::getFacadeRoot());
    }

    public function test_facade_delegates_to_client_resources(): void
    {
        $client = Tabby::getFacadeRoot();

        $this->assertSame($client, Tabby::getFacadeRoot());
        $this->assertSame($client->checkout(), $client->checkout());
        $this->assertSame($client->payments(), $client->payments());
        $this->assertSame($client->webhooks(), $client->webhooks());
    }
}
