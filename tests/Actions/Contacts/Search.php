<?php

/**
 * Test class to test rcmail_action_contacts_search
 *
 * @package Tests
 */
class Actions_Contacts_Search extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_search;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
