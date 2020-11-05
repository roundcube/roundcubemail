<?php

/**
 * Test class to test rcmail_action_contacts_import
 *
 * @package Tests
 */
class Actions_Contacts_Import extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run_init()
    {
        $action = new rcmail_action_contacts_import;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'import');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = [];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('contactimport', $output->template);
        $this->assertSame('Import contacts', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "rcmail.gui_object('importform', 'rcmImportForm');") !== false);
    }

    /**
     * Test run() method
     */
    function test_run_steps()
    {
        // TODO: Test all import steps
        $this->markTestIncomplete();
    }
}
