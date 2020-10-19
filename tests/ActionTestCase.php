<?php

/**
 * Test class to test rcmail_action_mail_index
 *
 * @package Tests
 */
class ActionTestCase extends PHPUnit\Framework\TestCase
{
    static function setUpBeforeClass()
    {
        $rcmail = rcmail::get_instance();
        $rcmail->load_gui();
        // $rcmail->storage_init(false);
    }
}
