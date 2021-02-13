<?php

/**
 * Test class to test rcmail_action_settings_upload
 *
 * @package Tests
 */
class Actions_Settings_Upload extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_upload;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
