<?php

/**
 * Test class to test rcmail_action_settings_response_edit
 *
 * @package Tests
 */
class Actions_Settings_ResponseEdit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_response_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
