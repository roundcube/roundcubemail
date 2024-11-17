<?php

namespace Roundcube\Tests\Actions\Settings;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_settings_folder_delete
 */
class FolderDeleteTest extends ActionTestCase
{
    /**
     * Test deleting a folder
     */
    public function test_delete()
    {
        $action = new \rcmail_action_settings_folder_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'folder-delete');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('delete_folder', true)
            ->registerFunction('get_quota', false);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('folder-delete', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully deleted.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_folder_row("Test");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.subscription_select();') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota(') !== false);
    }

    /**
     * Test handling errors
     */
    public function test_delete_errors()
    {
        $action = new \rcmail_action_settings_folder_delete();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'folder-delete');

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('delete_folder', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', \rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('folder-delete', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.remove_folder_row("Test");') === false);
    }
}
