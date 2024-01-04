<?php

/**
 * Test class to test rcmail_action_mail_folder_purge
 */
class Actions_Mail_FolderPurge extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_folder_purge();

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
