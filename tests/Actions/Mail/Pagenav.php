<?php

/**
 * Test class to test rcmail_action_mail_pagenav
 */
class Actions_Mail_Pagenav extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_pagenav();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
