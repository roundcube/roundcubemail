<?php

/**
 * Test class to test rcmail_action_mail_compose
 *
 * @package Tests
 */
class Actions_Mail_Compose extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_compose;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
