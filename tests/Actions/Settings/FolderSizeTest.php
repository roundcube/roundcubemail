<?php

/**
 * Test class to test rcmail_action_settings_folder_size
 */
class Actions_Settings_FolderSize extends ActionTestCase
{
    /**
     * Test getting a folder size
     */
    public function test_run()
    {
        $action = new rcmail_action_settings_folder_size();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-size');

        self::assertInstanceOf('rcmail_action', $action);
        self::assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('folder_size', 100);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('folder-size', $result['action']);
        self::assertSame('this.folder_size_update("100 B");', trim($result['exec']));
    }

    /**
     * Test handling errors
     */
    public function test_run_errors()
    {
        $action = new rcmail_action_settings_folder_size();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-size');

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('folder_size', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('folder-size', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
    }
}
