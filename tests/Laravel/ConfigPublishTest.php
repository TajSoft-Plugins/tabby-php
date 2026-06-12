<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Laravel;

final class ConfigPublishTest extends LaravelTestCase
{
    public function test_config_can_be_published(): void
    {
        $this->artisan('vendor:publish', [
            '--tag' => 'tabby-config',
            '--force' => true,
        ])->assertSuccessful();

        $publishedPath = $this->app->configPath('tabby.php');

        $this->assertFileExists($publishedPath);
        $this->assertStringContainsString('IS_TABBY_SANDBOX', (string) file_get_contents($publishedPath));
        $this->assertStringContainsString('TABBY_SANDBOX_SECRET_KEY', (string) file_get_contents($publishedPath));
    }
}
