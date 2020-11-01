<?php

/**
 * Test class to test rcmail_action_settings_folder_save
 *
 * @package Tests
 */
class Actions_Settings_FolderSave extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_save;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
