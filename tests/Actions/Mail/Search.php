<?php

/**
 * Test class to test rcmail_action_mail_search
 *
 * @package Tests
 */
class Actions_Mail_Search extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_search;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
