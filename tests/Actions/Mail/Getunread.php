<?php

/**
 * Test class to test rcmail_action_mail_getunread
 *
 * @package Tests
 */
class Actions_Mail_Getunread extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_getunread;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
