<?php

/**
 * Test class to test rcmail_action_settings_folder_save
 */
class Actions_Settings_FolderSave extends ActionTestCase
{
    /**
     * Test folder creation
     */
    public function test_new_folder()
    {
        $action = new rcmail_action_settings_folder_save();
        $output = $this->initOutput(rcmail_action::MODE_HTTP, 'settings', 'folder-save');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('get_capability', true)
            ->registerFunction('get_capability', true)
            ->registerFunction('folder_validate', true)
            ->registerFunction('mod_folder', 'NewTest')
            ->registerFunction('create_folder', true)
            ->registerFunction('mod_folder', 'NewTest')
            ->registerFunction('folder_info', [])
            ->registerFunction('folder_options', []);

        $_POST = ['_name' => 'NewTest'];

        $this->runAndAssert($action, OutputHtmlMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame('iframe', $output->template);
        self::assertTrue(stripos($result, '<!DOCTYPE html>') === 0);
        self::assertTrue(strpos($result, 'display_message("Folder created successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result, '.add_folder_row("NewTest"') !== false);
        self::assertTrue(strpos($result, '.subscription_select()') !== false);
    }

    /**
     * Test folder update/rename
     */
    public function test_folder_update()
    {
        self::markTestIncomplete();
    }

    /**
     * Test error handling
     */
    public function test_error_handling()
    {
        self::markTestIncomplete();
    }
}
