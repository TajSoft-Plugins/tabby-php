<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Laravel;

use Illuminate\Support\ServiceProvider;
use MustafaTaj\Tabby\Tabby;
use MustafaTaj\Tabby\TabbyClient;

final class TabbyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/tabby.php', 'tabby');

        $this->app->singleton('tabby', function ($app): TabbyClient {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('tabby', []);

            return Tabby::make($config);
        });

        $this->app->alias('tabby', TabbyClient::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/tabby.php' => $this->app->configPath('tabby.php'),
            ], 'tabby-config');
        }
    }
}
