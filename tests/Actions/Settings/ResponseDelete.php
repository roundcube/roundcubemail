<?php

/**
 * Test class to test rcmail_action_settings_response_delete
 *
 * @package Tests
 */
class Actions_Settings_ResponseDelete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_response_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
