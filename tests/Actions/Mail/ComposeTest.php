<?php

/**
 * Test class to test rcmail_action_mail_compose
 */
class Actions_Mail_Compose extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new rcmail_action_mail_compose();

        self::assertInstanceOf('rcmail_action', $object);
    }

    /**
     * Test quote_text() method
     */
    public function test_quote_text()
    {
        $action = new rcmail_action_mail_compose();

        self::assertSame('> ', $action->quote_text(''));

        $result = $action->quote_text("test1\ntest2");
        $expected = "> test1\n> test2";

        self::assertSame($expected, $result);

        $result = $action->quote_text("> test1\n> test2");
        $expected = ">> test1\n>> test2";

        self::assertSame($expected, $result);
    }
}
