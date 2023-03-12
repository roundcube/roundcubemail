<?php

/**
 * Test class to test rcmail_action_mail_check_recent
 *
 * @package Tests
 */
class Actions_Mail_CheckRecent extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_check_recent;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
