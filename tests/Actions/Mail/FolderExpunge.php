<?php

/**
 * Test class to test rcmail_action_mail_folder_expunge
 *
 * @package Tests
 */
class Actions_Mail_FolderExpunge extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_folder_expunge;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test expunging a folder
     */
    function test_folder_expunge()
    {
        $action = new rcmail_action_mail_folder_expunge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        $this->assertTrue($action->checks());

        $_POST = ['_mbox' => 'INBOX'];

        // Set expected storage function calls/results
        $storage = rcmail::get_instance()->storage;
        $storage->registerFunction('expunge_folder', true);
        $storage->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('expunge', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully compacted.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota(') === false);
    }

    /**
     * Test expunging a folder (with reload)
     */
    function test_folder_expunge_with_reload()
    {
        $action = new rcmail_action_mail_folder_expunge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        $this->assertTrue($action->checks());

        $_POST = ['_mbox' => 'INBOX'];
        $_REQUEST = ['_reload' => 1];

        // Set expected storage function calls/results
        $storage = rcmail::get_instance()->storage;
        $storage->registerFunction('expunge_folder', true);
        $storage->registerFunction('get_quota', false);

        $action->run();

        $commands = $output->getProperty('commands');

        $this->assertNull($output->getOutput());
        $this->assertSame('list', rcmail::get_instance()->action);
        $this->assertCount(3, $commands);
        $this->assertSame([
                'display_message',
                'Folder successfully compacted.',
                'confirmation',
                0
            ],
            $commands[0]
        );
        $this->assertSame('set_quota', $commands[1][0]);
        $this->assertSame('message_list.clear', $commands[2][0]);
    }

    /**
     * Test expunging error
     */
    function test_folder_expunge_error()
    {
        $action = new rcmail_action_mail_folder_expunge;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        $_POST = ['_mbox' => 'INBOX'];

        // Set expected storage function calls/results
        $storage = rcmail::get_instance()->storage;
        $storage->registerFunction('expunge_folder', false);
        $storage->registerFunction('get_error_code', -1);
        $storage->registerFunction('get_response_code', rcube_storage::READONLY);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('expunge', $result['action']);
        $this->assertSame('this.display_message("Unable to perform operation. Folder is read-only.","error",0);', trim($result['exec']));
    }
}
