<?php

/**
 * Test class to test rcmail_action_contacts_search_create
 *
 * @package Tests
 */
class Actions_Contacts_Search_Create extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_search_create;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
