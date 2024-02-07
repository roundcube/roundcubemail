<?php

/**
 * Test class to test rcmail_action_mail_autocomplete
 */
class Actions_Mail_Autocomplete extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_autocomplete();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
