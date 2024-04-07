<?php

/**
 * Test class to test rcmail_action_settings_about
 */
class Actions_Settings_About extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_about();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'about');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('about', $output->template);
        self::assertSame('About', $output->getProperty('pagetitle'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, 'This program is free software') !== false);
    }
}
