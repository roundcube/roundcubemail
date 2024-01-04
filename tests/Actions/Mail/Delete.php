<?php

/**
 * Test class to test rcmail_action_mail_delete
 */
class Actions_Mail_Delete extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_delete();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
