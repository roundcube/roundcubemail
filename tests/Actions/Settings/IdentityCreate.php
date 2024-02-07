<?php

/**
 * Test class to test rcmail_action_settings_identity_create
 */
class Actions_Settings_IdentityCreate extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_identity_create();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'add-identity');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('identityedit', $output->template);
        self::assertSame('Add identity', $output->getProperty('pagetitle'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
