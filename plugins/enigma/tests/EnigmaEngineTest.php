<?php

namespace Roundcube\Plugins\Tests;

use PHPUnit\Framework\TestCase;

class EnigmaEngineTest extends TestCase
{
    /**
     * Test password_handler()
     */
    public function test_password_handler()
    {
        $rcube = \rcube::get_instance();
        $engine = new \enigma_engine();

        unset($_SESSION['enigma_pass']);

        $engine->password_handler();

        $this->assertTrue(!array_key_exists('enigma_pass', $_SESSION));
        $this->assertSame([], $engine->get_passwords());

        $_POST = ['_keyid' => 'abc', '_passwd' => '123<a>456'];

        $time = time();
        $engine->password_handler();

        $store = unserialize($rcube->decrypt($_SESSION['enigma_pass']));

        $this->assertCount(2, $store['ABC']);
        $this->assertSame('123<a>456', $store['ABC'][0]);
        $this->assertEqualsWithDelta($time, $store['ABC'][1], 1);
        $this->assertSame(['ABC' => '123<a>456'], $engine->get_passwords());
    }
}
