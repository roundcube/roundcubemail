<?php

/**
 * Test class to test rcmail_action_mail_sendmdn
 */
class Actions_Mail_Sendmdn extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_sendmdn();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
