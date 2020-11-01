<?php

/**
 * Test class to test rcmail_action_settings_folder_edit
 *
 * @package Tests
 */
class Actions_Settings_FolderEdit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
