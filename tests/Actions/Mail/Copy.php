<?php

/**
 * Test class to test rcmail_action_mail_copy
 *
 * @package Tests
 */
class Actions_Mail_Copy extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_copy;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test copying a single message
     */
    function test_copy_message()
    {
        $action = new rcmail_action_mail_copy;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'copy');

        $this->assertTrue($action->checks());

        $_POST = [
            '_uid'         => 1,
            '_mbox'        => 'INBOX',
            '_target_mbox' => 'Trash'
        ];

        // Set expected storage function calls/results
        $storage = rcmail::get_instance()->storage;
        $storage->registerFunction('copy_message', true);
        $storage->registerFunction('count', 30);
        $storage->registerFunction('get_quota', false);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertTrue(strpos($result['exec'], 'this.display_message("Message(s) copied successfully.","confirmation",0);') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_unread_count("Trash",30,false,"");') !== false);
        $this->assertTrue(strpos($result['exec'], 'this.set_quota(') !== false);
    }

    /**
     * Test copying error
     */
    function test_copy_message_error()
    {
        $action = new rcmail_action_mail_copy;
        $output = $this->initOutput(rcmail_action::MODE_AJAX, 'mail', 'copy');

        $_POST = [
            '_uid'         => 1,
            '_mbox'        => 'INBOX',
            '_target_mbox' => 'Trash'
        ];

        // Set expected storage function calls/results
        $storage = rcmail::get_instance()->storage;
        $storage->registerFunction('copy_message', false);
        $storage->registerFunction('get_error_code', -1);
        $storage->registerFunction('get_response_code', rcube_storage::READONLY);

        $this->runAndAssert($action, OutputJsonMock::E_EXIT);

        $result = $output->getOutput();

        $this->assertSame(['Content-Type: application/json; charset=UTF-8'], $output->headers);
        $this->assertSame('copy', $result['action']);
        $this->assertSame('this.display_message("Unable to perform operation. Folder is read-only.","error",0);', trim($result['exec']));
    }
}
