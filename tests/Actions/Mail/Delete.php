<?php

/**
 * Test class to test rcmail_action_mail_delete
 *
 * @package Tests
 */
class Actions_Mail_Delete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
