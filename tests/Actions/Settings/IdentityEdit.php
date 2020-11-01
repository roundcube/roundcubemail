<?php

/**
 * Test class to test rcmail_action_settings_identity_edit
 *
 * @package Tests
 */
class Actions_Settings_IdentityEdit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_identity_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
