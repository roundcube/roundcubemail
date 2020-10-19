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
}
