<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Config\Region;
use MustafaTaj\Tabby\Tests\TestCase;

final class RegionTest extends TestCase
{
    public function test_ksa_maps_to_saudi_base_url(): void
    {
        $this->assertSame('https://api.tabby.sa', Region::KSA->baseUrl());
    }

    public function test_uae_maps_to_uae_base_url(): void
    {
        $this->assertSame('https://api.tabby.ai', Region::UAE->baseUrl());
    }

    public function test_kuwait_maps_to_uae_base_url(): void
    {
        $this->assertSame('https://api.tabby.ai', Region::KUWAIT->baseUrl());
    }

    public function test_try_from_string_is_case_insensitive(): void
    {
        $this->assertSame(Region::KSA, Region::tryFromString('KSA'));
        $this->assertSame(Region::UAE, Region::tryFromString('uae'));
    }
}
