<?php

/**
 * Test class to test rcmail_action_settings_identity_delete
 *
 * @package Tests
 */
class Actions_Settings_IdentityDelete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_identity_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
