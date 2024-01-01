<?php

/**
 * Test class to test rcmail_action_mail_headers
 */
class Actions_Mail_Headers extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_headers();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
