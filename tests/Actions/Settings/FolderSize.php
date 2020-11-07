<?php

/**
 * Test class to test rcmail_action_settings_folder_size
 *
 * @package Tests
 */
class Actions_Settings_FolderSize extends ActionTestCase
{
    /**
     * Test getting a folder size
     */
    function test_run()
    {
        $action = new rcmail_action_settings_folder_size;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-size');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('folder_size', 100);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-size', $result['action']);
        $this->assertSame('this.folder_size_update("100 B");', trim($result['exec']));
    }

    /**
     * Test handling errors
     */
    function test_run_errors()
    {
        $action = new rcmail_action_settings_folder_size;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-size');

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('folder_size', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-size', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
    }
}
