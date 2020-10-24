<?php

/**
 * Test class to test rcmail_action_contacts_show
 *
 * @package Tests
 */
class Actions_Contacts_Show extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_show;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
