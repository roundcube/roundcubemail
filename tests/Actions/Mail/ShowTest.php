<?php

/**
 * Test class to test rcmail_action_mail_show
 */
class Actions_Mail_Show extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_show();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
