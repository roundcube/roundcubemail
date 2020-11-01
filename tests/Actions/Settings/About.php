<?php

/**
 * Test class to test rcmail_action_settings_about
 *
 * @package Tests
 */
class Actions_Settings_About extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_about;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
