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

    /**
     * Test the reply body of an opaque S/MIME signed message (#10398)
     */
    public function test_prepare_message_body_smime_signed_data()
    {
        $file = TESTS_DIR . 'MessageRendering/data/greenmail/test-message-rendering@localhost/INBOX/smime-signed-data.eml';
        [, $body] = explode("\r\n\r\n", file_get_contents($file), 2);

        $part = new \rcube_message_part();
        $part->mime_id = '1';
        [$part->ctype_primary, $part->ctype_secondary] = explode('/', $part->mimetype = 'application/pkcs7-mime');
        $part->ctype_parameters = ['smime-type' => 'signed-data', 'name' => 'smime.p7m'];
        $part->encoding = 'base64';
        $part->size = strlen($body);
        $part->filename = 'smime.p7m';

        $action = new \rcmail_action_mail_compose();
        setProperty($action, 'MESSAGE', self::mockMessage($part, [base64_decode($body)]));
        setProperty($action, 'COMPOSE', ['mode' => \rcmail_sendmail::MODE_REPLY, 'id' => 'test']);

        $result = $action->prepare_message_body();

        $this->assertStringContainsString("\n> This message is signed, not encrypted. Grüße!", $result);
    }
}
