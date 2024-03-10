<?php

/**
 * Test class to test rcmail_action_settings_prefs_edit
 */
class Actions_Settings_PrefsEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_prefs_edit();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'edit-prefs');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_GET['_section'] = 'general';

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('settingsedit', $output->template);
        self::assertSame('Preferences', $output->getProperty('pagetitle'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
