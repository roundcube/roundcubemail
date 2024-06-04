<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class IdenticonEngineTest extends TestCase
{
    /**
     * Test icon generation
     */
    public function test_icon_generation()
    {
        if (!function_exists('imagepng')) {
            $this->markTestSkipped();
        }

        $engine = new \identicon_engine('test@domain.com', 10);

        $icon = $engine->getBinary();

        $this->assertMatchesRegularExpression('/^\x89\x50\x4E\x47/', $icon);
        $this->assertSame('image/png', $engine->getMimetype());
    }
}
