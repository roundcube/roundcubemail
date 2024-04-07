<?php

use PHPUnit\Framework\TestCase;

class Enigma_EnigmaEngine extends TestCase
{
    /**
     * Test password_handler()
     */
    public function test_password_handler()
    {
        $rcube = rcube::get_instance();
        $engine = new enigma_engine();

        unset($_SESSION['enigma_pass']);

        $engine->password_handler();

        self::assertTrue(!array_key_exists('enigma_pass', $_SESSION));
        self::assertSame([], $engine->get_passwords());

        $_POST = ['_keyid' => 'abc', '_passwd' => '123<a>456'];

        $time = time();
        $engine->password_handler();

        $store = unserialize($rcube->decrypt($_SESSION['enigma_pass']));

        self::assertCount(2, $store['ABC']);
        self::assertSame('123<a>456', $store['ABC'][0]);
        self::assertEqualsWithDelta($time, $store['ABC'][1], 1);
        self::assertSame(['ABC' => '123<a>456'], $engine->get_passwords());
    }
}
