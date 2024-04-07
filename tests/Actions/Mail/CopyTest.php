<?php

/**
 * Test class to test rcmail_action_mail_copy
 */
class Actions_Mail_Copy extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_copy();

        self::assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test copying a single message
     */
    public function test_copy_message()
    {
        $action = new rcmail_action_mail_copy();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'copy');

        self::assertTrue($action->checks());

        $_POST = [
            '_uid' => 1,
            '_mbox' => 'INBOX',
            '_target_mbox' => 'Trash',
        ];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('copy_message', true)
            ->registerFunction('count', 30)
            ->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('copy', $result['action']);
        self::assertTrue(strpos($result['exec'], 'this.display_message("Message(s) copied successfully.","confirmation",0);') !== false);
        self::assertTrue(strpos($result['exec'], 'this.set_unread_count("Trash",30,false,"");') !== false);
        self::assertTrue(strpos($result['exec'], 'this.set_quota(') !== false);
    }

    /**
     * Test copying error
     */
    public function test_copy_message_error()
    {
        $action = new rcmail_action_mail_copy();
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'copy');

        $_POST = [
            '_uid' => 1,
            '_mbox' => 'INBOX',
            '_target_mbox' => 'Trash',
        ];

        // Set expected storage function calls/results
        self::mockStorage()
            ->registerFunction('copy_message', false)
            ->registerFunction('get_error_code', -1)
            ->registerFunction('get_response_code', rcube_storage::READONLY);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        self::assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        self::assertSame('copy', $result['action']);
        self::assertSame('this.display_message("Unable to perform operation. Folder is read-only.","error",0);', trim($result['exec']));
    }
}
