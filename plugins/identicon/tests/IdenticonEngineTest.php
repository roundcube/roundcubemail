<?php

use PHPUnit\Framework\TestCase;

class Identicon_IdenticonEngine extends TestCase
{
    /**
     * Test icon generation
     */
    public function test_icon_generation()
    {
        if (!function_exists('imagepng')) {
            self::markTestSkipped();
        }

        $engine = new identicon_engine('test@domain.com', 10);

        $icon = $engine->getBinary();

        self::assertMatchesRegularExpression('/^\x89\x50\x4E\x47/', $icon);
        self::assertSame('image/png', $engine->getMimetype());
    }
}
