<?php

/**
 * Test class to test rcmail_action_mail_folder_expunge
 */
class Actions_Mail_FolderExpunge extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_folder_expunge();

        self::assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test expunging a folder
     */
    public function test_folder_expunge()
    {
        $action = new rcmail_action_mail_folder_expunge();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        self::assertTrue($action->checks());

        $_POST = ['_mbox' => 'INBOX'];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('expunge_folder', true)
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('expunge', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Folder successfully compacted.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.set_quota(') === false);
    }

    /**
     * Test expunging a folder (with reload)
     */
    public function test_folder_expunge_with_reload()
    {
        $action = new rcmail_action_mail_folder_expunge();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        self::assertTrue($action->checks());

        $_POST = ['_mbox' => 'INBOX'];
        $_REQUEST = ['_reload' => 1];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('expunge_folder', true)
            ->registerFunction('get_quota', false);

        $action->run();

        $commands = $output->getProperty('commands');

        self::assertNull($output->getOutput());
        self::assertSame('list', rcmail::get_instance()->action);
        self::assertCount(3, $commands);
        self::assertSame([
                'display_message',
                'Folder successfully compacted.',
                'confirmation',
                0,
            ],
            $commands[0]
        );
        self::assertSame('set_quota', $commands[1][0]);
        self::assertSame('message_list.clear', $commands[2][0]);
    }

    /**
     * Test expunging error
     */
    public function test_folder_expunge_error()
    {
        $action = new rcmail_action_mail_folder_expunge();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'expunge');

        $_POST = ['_mbox' => 'INBOX'];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('expunge_folder', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('expunge', $result['action']);
        self::assertSame('this.display_message("Unable to perform operation. Folder is read-only.","error",0);', trim($result['exec']));
    }
}
