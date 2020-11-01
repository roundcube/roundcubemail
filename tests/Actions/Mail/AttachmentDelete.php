<?php

/**
 * Test class to test rcmail_action_mail_attachment_delete
 *
 * @package Tests
 */
class Actions_Mail_AttachmentDelete extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_attachment_delete;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
