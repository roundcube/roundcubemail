<?php

/**
 * Test class to test rcmail_action_utils_error
 *
 * @package Tests
 */
class Actions_Utils_Error extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_error;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
