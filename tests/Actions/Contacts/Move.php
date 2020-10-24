<?php

/**
 * Test class to test rcmail_action_contacts_move
 *
 * @package Tests
 */
class Actions_Contacts_Move extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_move;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
