<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;

/**
 * Test class to test rcmail_action_mail_compose
 */
class ComposeTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_compose();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }

    /**
     * Test quote_text() method
     */
    public function test_quote_text()
    {
        $action = new \rcmail_action_mail_compose();

        $this->assertSame('> ', $action->quote_text(''));

        $result = $action->quote_text("test1\ntest2");
        $expected = "> test1\n> test2";

        $this->assertSame($expected, $result);

        $result = $action->quote_text("> test1\n> test2");
        $expected = ">> test1\n>> test2";

        $this->assertSame($expected, $result);
    }

    /**
     * Test rcmail_action_mail_compose::get_reply_header()
     */
    public function test_get_reply_header()
    {
        $rcmail = \rcmail::get_instance();
        $rcmail->config->set('date_long', 'Y-m-d H:i');

        // The date must be presented in the sender's timezone with an explicit offset (#7352)
        $message = (new \ReflectionClass(\rcube_message::class))->newInstanceWithoutConstructor();
        $message->headers = new \rcube_message_header();
        $message->headers->set('From', 'Steve Jobs <steve@example.com>');
        $message->headers->set('Date', 'Tue, 28 Apr 2020 10:35:20 +0900');

        $result = \rcmail_action_mail_compose::get_reply_header($message);

        $this->assertSame('On 2020-04-28 10:35 +0900, Steve Jobs wrote:', $result);
    }
}
