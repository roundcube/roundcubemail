<?php

/**
 * Test class to test rcube_session class
 *
 * @package Tests
 */
class Framework_Session extends PHPUnit\Framework\TestCase
{
    /**
     * Test factory method
     */
    function test_factory()
    {
        $rcube = rcube::get_instance();

        // We cannot test DB session handler as it's initialization
        // will collide with already sent headers. Let's try php session.
        $rcube->config->set('session_storage', 'php');

        $session = rcube_session::factory($rcube->config);

        $this->assertInstanceOf('rcube_session_php', $session);

        // This method should not do any harm, just call it and expect no errors
        $session->reload();
    }

    /**
     * Test unserialize() method
     */
    function test_unserialize()
    {
        $rcube = rcube::get_instance();

        $rcube->config->set('session_storage', 'php');

        $session = rcube_session::factory($rcube->config);

        $this->assertSame([], $session->unserialize(''));
        $this->assertSame(
            ['ok' => true, 'name' => 'me', 'int' => 34],
            $session->unserialize('ok|b:1;name|s:2:"me";int|i:34;')
        );
    }
}
