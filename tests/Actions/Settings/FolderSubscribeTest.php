<?php

namespace Roundcube\Tests\Actions\Settings;

use rcmail_action as rcmail_action;
use rcmail_action_settings_folder_subscribe as rcmail_action_settings_folder_subscribe;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_settings_folder_subscribe
 */
class FolderSubscribeTest extends ActionTestCase
{
    /**
     * Test subscribing a folder
     */
    public function test_subscribe()
    {
        $action = new \rcmail_action_settings_folder_subscribe();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'folder-subscribe');

        $this->assertInstanceOf(\rcmail_action::class, $action);
        $this->assertTrue($action->checks());

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('subscribe', true)
            ->registerFunction('is_special_folder', false);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('folder-subscribe', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully subscribed.","confirmation",0);') !== false);

        // TODO: Test a special folder subscription
    }

    /**
     * Test handling errors
     */
    public function test_subscribe_errors()
    {
        $action = new \rcmail_action_settings_folder_subscribe();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'settings', 'folder-subscribe');

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('subscribe', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', \rcube_storage::READONLY)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', \rcube_storage::READONLY);

        $_POST = ['_mbox' => 'Test'];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('folder-subscribe', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Unable to perform operation. Folder is read-only.","error",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.reset_subscription("Test",false);') !== false);

        // TODO: Test TRYCREATE error handling
    }
}
