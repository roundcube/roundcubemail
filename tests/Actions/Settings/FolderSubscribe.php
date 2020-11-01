<?php

/**
 * Test class to test rcmail_action_settings_folder_subscribe
 *
 * @package Tests
 */
class Actions_Settings_FolderSubscribe extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_folder_subscribe;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
