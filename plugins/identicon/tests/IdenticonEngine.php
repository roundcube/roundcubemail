<?php

class Identicon_IdenticonEngine extends PHPUnit\Framework\TestCase
{
    public static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../identicon_engine.php';
    }

    /**
     * Test icon generation
     */
    function test_icon_generation()
    {
        if (!function_exists('imagepng')) {
            $this->markTestSkipped();
        }

        $engine = new identicon_engine('test@domain.com', 10);

        $icon = $engine->getBinary();

        $this->assertMatchesRegularExpression('/^\x89\x50\x4E\x47/', $icon);
        $this->assertSame('image/png', $engine->getMimetype());
    }
}

