<?php

class Enigma_EnigmaEngine extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass(): void
    {
        include_once __DIR__ . '/../enigma.php';
        include_once __DIR__ . '/../lib/enigma_engine.php';
    }

    /**
     * Test password_handler()
     */
    function test_password_handler()
    {
        $rcube  = rcube::get_instance();
        $plugin = new enigma($rcube->plugins);
        $engine = new enigma_engine($plugin);

        unset($_SESSION['enigma_pass']);

        $engine->password_handler();

        $this->assertTrue(!array_key_exists('enigma_pass', $_SESSION));
        $this->assertSame([], $engine->get_passwords());

        $_POST = ['_keyid' => 'abc', '_passwd' => '123<a>456'];

        $time = time();
        $engine->password_handler();

        $store = unserialize($rcube->decrypt($_SESSION['enigma_pass']));

        $this->assertSame(['123<a>456', $time], $store['ABC']);
        $this->assertSame(['ABC' => '123<a>456'], $engine->get_passwords());
    }
}

