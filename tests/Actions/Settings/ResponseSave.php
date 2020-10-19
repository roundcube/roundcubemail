<?php

/**
 * Test class to test rcmail_action_settings_response_save
 *
 * @package Tests
 */
class Actions_Settings_ResponseSave extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_response_save;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
