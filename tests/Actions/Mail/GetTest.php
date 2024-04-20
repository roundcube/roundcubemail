<?php

/**
 * Test class to test rcmail_action_mail_get
 */
class Actions_Mail_Get extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_get();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
