<?php

/**
 * Test class to test rcmail_action_settings_folders
 *
 * @package Tests
 */
class Actions_Settings_Folders extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folders;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
