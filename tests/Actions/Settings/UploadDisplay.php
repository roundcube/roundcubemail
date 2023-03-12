<?php

/**
 * Test class to test rcmail_action_settings_upload_display
 *
 * @package Tests
 */
class Actions_Settings_UploadDisplay extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_upload_display;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
