<?php

/**
 * Test class to test rcmail_action_settings_folder_create
 */
class Actions_Settings_FolderCreate extends ActionTestCase
{
    /**
     * Test run() method
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_folder_create();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'folder-create');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('get_capability', true)
            ->registerFunction('get_capability', true)
            ->registerFunction('folder_info', [
                'name' => 'Test',
                'is_root' => false,
                'noselect' => false,
                'special' => false,
                'namespace' => 'personal',
            ])
            ->registerFunction('list_folders', [
                'INBOX',
                'Test',
            ])
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('folder_attributes', [])
            ->registerFunction('count', 0)
            ->registerFunction('get_namespace', null)
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('folderedit', $output->template);
        self::assertSame('', $output->getProperty('pagetitle')); // TODO: It should have some title
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, "rcmail.gui_object('editform', 'form');") !== false);
    }
}
