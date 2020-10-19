<?php

/**
 * Test class to test rcmail_action_settings_folder_unsubscribe
 *
 * @package Tests
 */
class Actions_Settings_FolderUnsubscribe extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_unsubscribe;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
