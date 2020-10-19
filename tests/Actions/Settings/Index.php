<?php

/**
 * Test class to test rcmail_action_settings_index
 *
 * @package Tests
 */
class Actions_Settings_Index extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_index;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
