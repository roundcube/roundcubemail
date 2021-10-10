<?php

/**
 * Test class to test rcmail_action_mail_compose
 *
 * @package Tests
 */
class Actions_Mail_Compose extends ActionTestCase
{
    /**
     * Class constructor
     */
    function test_class()
    {
        $object = new rcmail_action_mail_compose;

        $this->assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test wrap_and_quote() method
     */
    function test_wrap_and_quote()
    {
        $action = new rcmail_action_mail_compose;

        $this->assertSame('> ', $action->wrap_and_quote(''));
        $this->assertSame('', $action->wrap_and_quote('', 72, false));

        $result = $action->wrap_and_quote("test1\ntest2");
        $expected = "> test1\n> test2";

        $this->assertSame($expected, $result);

        $result = $action->wrap_and_quote("> test1\n> test2");
        $expected = ">> test1\n>> test2";

        $this->assertSame($expected, $result);
    }
}
