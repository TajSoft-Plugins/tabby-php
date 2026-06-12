<?php

declare(strict_types=1);

namespace MustafaTaj\Tabby\Tests\Unit;

use MustafaTaj\Tabby\Support\BooleanParser;
use PHPUnit\Framework\TestCase;

final class BooleanParserTest extends TestCase
{
    public function test_string_false_is_not_truthy(): void
    {
        $this->assertFalse(BooleanParser::resolve('false'));
        $this->assertFalse(BooleanParser::resolve('0'));
        $this->assertFalse(BooleanParser::resolve('no'));
        $this->assertFalse(BooleanParser::resolve('off'));
    }

    public function test_string_true_values(): void
    {
        $this->assertTrue(BooleanParser::resolve('true'));
        $this->assertTrue(BooleanParser::resolve('1'));
        $this->assertTrue(BooleanParser::resolve('yes'));
        $this->assertTrue(BooleanParser::resolve('on'));
    }
}
