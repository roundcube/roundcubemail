<?php

/**
 * Test class to test rcmail_action_mail_move
 */
class Actions_Mail_Move extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_move();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
