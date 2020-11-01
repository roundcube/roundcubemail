<?php

/**
 * Test class to test rcmail_action_settings_folder_rename
 *
 * @package Tests
 */
class Actions_Settings_FolderRename extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_rename;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
