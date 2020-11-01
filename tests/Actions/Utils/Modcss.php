<?php

/**
 * Test class to test rcmail_action_utils_modcss
 *
 * @package Tests
 */
class Actions_Utils_Modcss extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_modcss;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
