<?php

/**
 * Test class to test rcmail_action_mail_autocomplete
 *
 * @package Tests
 */
class Actions_Mail_Autocomplete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_autocomplete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
