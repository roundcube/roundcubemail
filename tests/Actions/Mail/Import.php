<?php

/**
 * Test class to test rcmail_action_mail_import
 *
 * @package Tests
 */
class Actions_Mail_Import extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_import;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
