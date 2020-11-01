<?php

/**
 * Test class to test rcmail_action_utils_killcache
 *
 * @package Tests
 */
class Actions_Utils_Killcache extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_killcache;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
