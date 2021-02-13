<?php

/**
 * Test class to test rcmail_action_settings_response_create
 *
 * @package Tests
 */
class Actions_Settings_ResponseCreate extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_response_create;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'add-response');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $_GET = [];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('responseedit', $output->template);
        $this->assertSame('Add response', $output->getProperty('pagetitle'));
        $this->assertSame(false, $output->get_env('readonly'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('editform', 'form')") !== false);
    }
}
