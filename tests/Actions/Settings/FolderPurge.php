<?php

/**
 * Test class to test rcmail_action_settings_folder_purge
 *
 * @package Tests
 */
class Actions_Settings_FolderPurge extends ActionTestCase
{
    /**
     * Test purging a folder
     */
    function test_purge()
    {
        $action = new rcmail_action_settings_folder_purge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-purge');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('move_message', true)
            ->registerFunction('get_quota', false);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-purge', $result['action']);
        $this->assertSame(0, $result['env']['messagecount']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Message(s) moved successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.show_folder("Test",null,true);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota') === false);
    }

    /**
     * Test purging a Trash folder
     */
    function test_purge_trash()
    {
        $action = new rcmail_action_settings_folder_purge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-purge');

        $this->assertInstanceOf('rcmail_action', $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('delete_message', true)
            ->registerFunction('get_quota', false);

        $_POST = ['_mbox' => 'Trash'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-purge', $result['action']);
        $this->assertSame(0, $result['env']['messagecount']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully emptied.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.show_folder("Trash",null,true);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota') !== false);
    }

    /**
     * Test handling errors
     */
    function test_purge_errors()
    {
        $action = new rcmail_action_settings_folder_purge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'settings', 'folder-purge');

        // Set expected storage function calls/results
        rcmail::get_instance()->storage
            ->registerFunction('move_message', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('folder-purge', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
    }
}
