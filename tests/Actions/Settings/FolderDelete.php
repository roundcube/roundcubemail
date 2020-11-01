<?php

/**
 * Test class to test rcmail_action_settings_folder_delete
 *
 * @package Tests
 */
class Actions_Settings_FolderDelete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
