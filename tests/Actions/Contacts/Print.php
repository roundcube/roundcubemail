<?php

/**
 * Test class to test rcmail_action_contacts_print
 *
 * @package Tests
 */
class Actions_Contacts_Print extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_print;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
