<?php

/**
 * Test class to test rcmail_action_mail_attachment_upload
 *
 * @package Tests
 */
class Actions_Mail_Attachmentupload extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_attachment_upload;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
