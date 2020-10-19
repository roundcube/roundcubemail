<?php

/**
 * Test class to test rcmail_action_mail_folder_expunge
 *
 * @package Tests
 */
class Actions_Mail_FolderExpunge extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_folder_expunge;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
