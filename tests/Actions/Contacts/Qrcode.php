<?php

/**
 * Test class to test rcmail_action_contacts_qrcode
 *
 * @package Tests
 */
class Actions_Contacts_Qrcode extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_qrcode;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
