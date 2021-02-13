<?php

/**
 * Test class to test rcmail_action_utils_save_pref
 *
 * @package Tests
 */
class Actions_Utils_SavePref extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_utils_save_pref;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
