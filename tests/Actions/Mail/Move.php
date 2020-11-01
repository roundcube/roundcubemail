<?php

/**
 * Test class to test rcmail_action_mail_move
 *
 * @package Tests
 */
class Actions_Mail_Move extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_move;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
