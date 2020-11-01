<?php

/**
 * Test class to test rcmail_action_settings_prefs_edit
 *
 * @package Tests
 */
class Actions_Settings_PrefsEdit extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_settings_prefs_edit;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
