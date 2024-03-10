<?php

/**
 * Test class to test rcmail_action_mail_viewsource
 */
class Actions_Mail_Viewsource extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_viewsource();

        self::assertInstanceOf('rcmail_action', $object);
    }
}
