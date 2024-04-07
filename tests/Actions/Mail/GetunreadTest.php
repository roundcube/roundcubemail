<?php

/**
 * Test class to test rcmail_action_mail_getunread
 */
class Actions_Mail_Getunread extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_getunread();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
