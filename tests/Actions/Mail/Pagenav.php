<?php

/**
 * Test class to test rcmail_action_mail_pagenav
 *
 * @package Tests
 */
class Actions_Mail_Pagenav extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_pagenav;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
