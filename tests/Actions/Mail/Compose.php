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
     * Test quote_text() method
     */
    function test_quote_text()
    {
        $action = new rcmail_action_mail_compose;

        $this->assertSame('> ', $action->quote_text(''));

        $result = $action->quote_text("test1\ntest2");
        $expected = "> test1\n> test2";

        $this->assertSame($expected, $result);

        $result = $action->quote_text("> test1\n> test2");
        $expected = ">> test1\n>> test2";

        $this->assertSame($expected, $result);
    }
}
