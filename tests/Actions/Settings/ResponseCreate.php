<?php

/**
 * Test class to test rcmail_action_settings_response_create
 *
 * @package Tests
 */
class Actions_Settings_ResponseCreate extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_response_create;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
