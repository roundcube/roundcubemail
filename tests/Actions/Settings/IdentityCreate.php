<?php

/**
 * Test class to test rcmail_action_settings_identity_create
 *
 * @package Tests
 */
class Actions_Settings_IdentityCreate extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_identity_create;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
