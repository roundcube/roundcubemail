<?php

/**
 * Test class to test rcmail_action_mail_group_expand
 */
class Actions_Mail_GroupExpand extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_group_expand();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
