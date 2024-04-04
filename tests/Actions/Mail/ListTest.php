<?php

/**
 * Test class to test rcmail_action_mail_list
 */
class Actions_Mail_List extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_list();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
