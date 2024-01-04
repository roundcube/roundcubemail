<?php

/**
 * Test class to test rcmail_action_mail_mark
 */
class Actions_Mail_Mark extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_mark();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
