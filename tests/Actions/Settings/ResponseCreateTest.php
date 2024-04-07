<?php

/**
 * Test class to test rcmail_action_settings_response_create
 */
class Actions_Settings_ResponseCreate extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_response_create();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'add-response');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $_GET = [];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('responseedit', $output->template);
        self::assertSame('Add response', $output->getProperty('pagetitle'));
        self::assertFalse($output->get_env('readonly'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
