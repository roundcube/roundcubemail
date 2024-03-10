<?php

/**
 * Test class to test rcmail_action_settings_upload_display
 */
class Actions_Settings_UploadDisplay extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_settings_upload_display();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
