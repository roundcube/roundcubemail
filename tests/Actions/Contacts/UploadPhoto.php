<?php

/**
 * Test class to test rcmail_action_contacts_upload_photo
 *
 * @package Tests
 */
class Actions_Contacts_Upload_Photo extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_contacts_upload_photo;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
