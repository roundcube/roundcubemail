<?php

/**
 * Test class to test rcmail_action_mail_show
 *
 * @package Tests
 */
class Actions_Mail_Show extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_show;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
