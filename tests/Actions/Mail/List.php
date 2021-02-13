<?php

/**
 * Test class to test rcmail_action_mail_list
 *
 * @package Tests
 */
class Actions_Mail_List extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_list;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
