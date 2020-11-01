<?php

/**
 * Test class to test rcmail_action_settings_folder_size
 *
 * @package Tests
 */
class Actions_Settings_FolderSize extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_size;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
