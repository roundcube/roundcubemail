<?php

/**
 * Test class to test rcmail_action_settings_responses
 *
 * @package Tests
 */
class Actions_Settings_Responses extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_responses;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
