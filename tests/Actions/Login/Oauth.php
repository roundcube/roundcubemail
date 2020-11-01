<?php

/**
 * Test class to test rcmail_action_login_oauth
 *
 * @package Tests
 */
class Actions_Login_Oauth extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_login_oauth;

        $this->assertInstanceOf('rcmail_action', $object);
    }
}
