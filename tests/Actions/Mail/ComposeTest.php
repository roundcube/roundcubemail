<?php

namespace Roundcube\Tests\Actions\Mail;

use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\MessageMock;

use function Roundcube\Tests\setProperty;

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
     * Test compose_part_body() handling of text/enriched content
     */
    public function test_compose_part_body_enriched()
    {
        $message = new MessageMock(123);
        $set_part = static function ($body) use ($message) {
            $part = new \rcube_message_part();
            $part->mime_id = '1';
            [$part->ctype_primary, $part->ctype_secondary] = explode('/', $part->mimetype = 'text/enriched');
            $message->set_part_body(1, $body);
            return $part;
        };

        $action = new \rcmail_action_mail_compose();
        setProperty($action, 'MESSAGE', $message);
        setProperty($action, 'COMPOSE', ['mode' => \rcmail_sendmail::MODE_NONE]);

        $part = $set_part('<italic>the-text</italic>Test<script>alert(1)</script>');
        $result = $action->compose_part_body($part, true);
        $this->assertSame('<div id="nonebody1"><i>the-text</i>Test</div>', $result);

        $part = $set_part('<italic>the-text</italic>Test<script>alert(1)</script>');
        $result = $action->compose_part_body($part, false);
        $this->assertSame('_the-text_Test', $result);
    }

    /**
     * Test compose_part_body() handling of text/markdown content
     */
    public function test_compose_part_body_markdown()
    {
        $message = new MessageMock(123);
        $set_part = static function ($body) use ($message) {
            $part = new \rcube_message_part();
            $part->mime_id = '1';
            [$part->ctype_primary, $part->ctype_secondary] = explode('/', $part->mimetype = 'text/markdown');
            $message->set_part_body(1, $body);
            return $part;
        };

        $action = new \rcmail_action_mail_compose();
        setProperty($action, 'MESSAGE', $message);
        setProperty($action, 'COMPOSE', ['mode' => \rcmail_sendmail::MODE_NONE]);

        $part = $set_part('*test* it!<img src=x onerror=\'alert("vulnerable!")\'/>');
        $result = $action->compose_part_body($part, true);
        $this->assertSame('<p><em>test</em> it!</p>', trim($result));

        $part = $set_part('*test* it!<img src=x onerror=\'alert("vulnerable!")\'/>');
        $result = $action->compose_part_body($part, false);
        $this->assertSame('_test_ it!', $result);
    }
}
