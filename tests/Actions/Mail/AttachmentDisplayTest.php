<?php

/**
 * Test class to test rcmail_action_mail_attachment_display
 */
class Actions_Mail_AttachmentDisplay extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_attachment_display();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
