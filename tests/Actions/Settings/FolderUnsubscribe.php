<?php

/**
 * Test class to test rcmail_action_settings_folder_unsubscribe
 *
 * @package Tests
 */
class Actions_Settings_FolderUnsubscribe extends ActionTestCase
{
    /**
     * Test unsubscribing a folder
     */
    function test_unsubscribe()
    {
        $action = new rcmail_action_settings_folder_unsubscribe;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-unsubscribe');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('unsubscribe', true)
            ->registerFunction('is_special_folder', false);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-unsubscribe', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully unsubscribed.","confirmation",0);') !== false);
    }

    /**
     * Test handling errors
     */
    function test_unsubscribe_errors()
    {
        $action = new rcmail_action_settings_folder_unsubscribe;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-unsubscribe');

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('unsubscribe', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-unsubscribe', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.reset_subscription("Test",true);') !== false);
    }
}
