<?php

/**
 * Test class to test rcmail_action_mail_attachment_rename
 *
 * @package Tests
 */
class Actions_Mail_AttachmentRename extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_attachment_rename;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
