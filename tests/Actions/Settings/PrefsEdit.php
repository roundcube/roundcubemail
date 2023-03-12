<?php

/**
 * Test class to test rcmail_action_settings_prefs_edit
 *
 * @package Tests
 */
class Actions_Settings_PrefsEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_prefs_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-prefs');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $_GET['_section'] = 'general';

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('settingsedit', $output->template);
        $this->assertSame('Preferences', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
