<?php

/**
 * Test class to test rcmail_action_mail_attachment_display
 *
 * @package Tests
 */
class Actions_Mail_AttachmentDisplay extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_attachment_display;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
