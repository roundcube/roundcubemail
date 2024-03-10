<?php

/**
 * Test class to test rcmail_action_contacts_import
 */
class Actions_Contacts_Import extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run_init()
    {
        $action = new rcmail_action_contacts_import();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'contacts', 'import');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        self::initDB('contacts');

        $_GET = [];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('contactimport', $output->template);
        self::assertSame('Import contacts', $output->getProperty('pagetitle'));
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('importform', 'rcmImportForm');") !== false);
    }

    /**
     * Test run() method
     */
    public function test_run_steps()
    {
        // TODO: Test all import steps
        self::markTestIncomplete();
    }
}
