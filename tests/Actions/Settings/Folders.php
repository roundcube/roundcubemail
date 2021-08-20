<?php

/**
 * Test class to test rcmail_action_settings_folders
 *
 * @package Tests
 */
class Actions_Settings_Folders extends ActionTestCase
{
    /**
     * Test run() method
     */
    function test_run()
    {
        $action = new rcmail_action_settings_folders;
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'folders');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('clear_cache', true)
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
            ->registerFunction('list_folders_subscribed', [
                    'INBOX',
                    'Test',
            ])
            ->registerFunction('get_special_folders', [])
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('mod_folder', 'Test')
            ->registerFunction('folder_attributes', [])
            ->registerFunction('count', 0)
            ->registerFunction('get_namespace', null)
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame('folders', $output->template);
        $this->assertSame('Folders', $output->getProperty('pagetitle'));
        $this->assertTrue(stripos($result, "<!DOCTYPE html>") === 0);
        $this->assertMatchesRegularExpression('/treelist(.min)?.js/', $result);
    }

    /**
     * Test folder_subscriptions() method
     */
    function test_folder_subscriptions()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test folder_filter() method
     */
    function test_folder_filter()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test folder_options() method
     */
    function test_folder_options()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test update_folder_row() method
     */
    function test_update_folder_row()
    {
        $this->markTestIncomplete();
    }
}
