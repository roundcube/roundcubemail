<?php

/**
 * Test class to test rcmail_action_settings_folder_rename
 *
 * @package Tests
 */
class Actions_Settings_FolderRename extends ActionTestCase
{
    /**
     * Test renaming a folder
     */
    function test_rename()
    {
        $action = new rcmail_action_settings_folder_rename;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-rename');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('rename_folder', true)
            ->registerFunction('folder_info', [])
            ->registerFunction('mod_folder', 'Test2');

        $_POST = ['_folder_oldname' => 'Test', '_folder_newname' => 'Test2'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-rename', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.replace_folder_row("Test","Test2","Test2","Test2",false,"mailbox");') !== false);
    }

    /**
     * Test handling errors
     */
    function test_rename_errors()
    {
        $action = new rcmail_action_settings_folder_rename;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-rename');

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('rename_folder', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $_POST = ['_folder_oldname' => 'Test', '_folder_newname' => 'Test2'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-rename', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
    }
}
