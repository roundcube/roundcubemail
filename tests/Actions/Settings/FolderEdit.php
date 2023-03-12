<?php

/**
 * Test class to test rcmail_action_settings_folder_edit
 *
 * @package Tests
 */
class Actions_Settings_FolderEdit extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_folder_edit;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'folder-edit');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('get_capability', true)
            ->registerFunction('get_capability', true)
            ->registerFunction('folder_info', [
                    'name'      => 'Test',
                    'is_root'   => false,
                    'noselect'  => false,
                    'special'   => false,
                    'namespace' => 'personal',
            ])
            ->registerFunction('list_folders', [
                    'INBOX',
                    'Test',
            ])
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('folder_attributes', [])
            ->registerFunction('count', 0)
            ->registerFunction('get_namespace', null)
            ->registerFunction('get_quota', false);

        $_GET = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('folderedit', $output->template);
        $this->assertSame('', $output->getProperty('pagetitle')); // TODO: It should have some title
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertTrue(strpos($result, "Folder properties") !== false);
    }

    /**
     * Test folder_form() method
     */
    function test_folder_form()
    {
        $this->markTestIncomplete();
    }
}
