<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\OutputJsonMock;

/**
 * Test class to test rcmail_action_mail_folder_purge
 */
class FolderPurgeTest extends ActionTestCase
{
    /**
     * Test security check (folder is required)
     */
    public function test_run_empty_post()
    {
        $action = new \rcmail_action_mail_folder_purge();
        $output = $this->initOutput(\rcmail_action::MODE_AJAX, 'mail', 'purge');

        $this->assertTrue($action->checks());

        $_POST = [];

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertContains('Content-Type: application/json; charset=UTF-8', $output->headers);
        $this->assertSame('purge', $result['action']);
        $this->assertStringContainsString('this.display_message("An internal error occurred', $result['exec']);
    }
}
