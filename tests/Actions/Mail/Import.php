<?php

/**
 * Test class to test rcmail_action_mail_import
 */
class Actions_Mail_Import extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_import();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
