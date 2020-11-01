<?php

/**
 * Test class to test rcmail_action_settings_identities
 *
 * @package Tests
 */
class Actions_Settings_Identities extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_identities;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
