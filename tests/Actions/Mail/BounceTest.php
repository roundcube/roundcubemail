<?php

/**
 * Test class to test rcmail_action_mail_bounce
 */
class Actions_Mail_Bounce extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_bounce();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
