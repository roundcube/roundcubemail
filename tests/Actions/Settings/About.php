<?php

/**
 * Test class to test rcmail_action_settings_about
 *
 * @package Tests
 */
class Actions_Settings_About extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_about;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'about');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('about', $output->template);
        $this->assertSame('About', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "This program is free software") !== false);
    }
}
