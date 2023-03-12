<?php

/**
 * Test class to test rcmail_action_mail_viewsource
 *
 * @package Tests
 */
class Actions_Mail_Viewsource extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_viewsource;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
