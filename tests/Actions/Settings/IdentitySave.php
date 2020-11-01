<?php

/**
 * Test class to test rcmail_action_settings_identity_save
 *
 * @package Tests
 */
class Actions_Settings_IdentitySave extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_identity_save;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
