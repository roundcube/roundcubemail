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
     * Test write_compose_attachments() handling of inline attachments
     */
    public function test_write_compose_attachments_inline()
    {
        // Non-image parts marked as inline in a multipart/mixed message with a HTML part (#8650)
        $message = $this->loadMessage('inline-attachments.eml');
        $expected = ['file1.txt', 'file2.txt', 'file3.pdf'];

        $body = '<p>html message bla bla bla</p>';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_FORWARD, true, $body);
        $this->assertSame($expected, $result);

        $body = 'html message bla bla bla';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_FORWARD, false, $body);
        $this->assertSame($expected, $result);

        $body = '<p>html message bla bla bla</p>';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_REPLY, true, $body);
        $this->assertSame([], $result);

        // The HTML body part is not attached, also when marked as inline
        $message->mime_parts['1']->disposition = 'inline';

        $body = '<p>html message bla bla bla</p>';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_FORWARD, true, $body);
        $this->assertSame($expected, $result);

        // An inline image used in the HTML body is attached once and only in HTML mode
        $message = $this->loadMessage('html.msg');
        $cid_map = \rcmail_action_mail_compose::cid_map($message);
        $placeholder = $cid_map['cid:1435387308558e45acc0292830140312@domain.tld'];

        $body = '<p><img src="' . $placeholder . '" /></p>';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_FORWARD, true, $body);
        $this->assertSame(['photo-mini.jpg'], $result);
        $this->assertStringNotContainsString($placeholder, $body);
        $this->assertStringContainsString('_action=display-attachment&_file=rcmfile', $body);

        $body = 'this is test';
        $result = $this->writeComposeAttachments($message, \rcmail_sendmail::MODE_FORWARD, false, $body);
        $this->assertSame([], $result);
    }

    /**
     * Create a message object from a message file, without IMAP
     */
    private function loadMessage($file)
    {
        $headers = new \rcube_message_header();
        $headers->structure = \rcube_mime::parse_message(file_get_contents(TESTS_DIR . 'src/' . $file));

        self::mockStorage()
            ->registerFunction('set_folder')
            ->registerFunction('set_options')
            ->registerFunction('get_message', $headers);

        return new \rcube_message(123, 'INBOX');
    }

    /**
     * Call write_compose_attachments() and return names of the attached files
     */
    private function writeComposeAttachments($message, $mode, $is_html, &$body)
    {
        $rcmail = \rcmail::get_instance();
        $action = new \rcmail_action_mail_compose();
        $compose_id = uniqid();

        setProperty($action, 'COMPOSE_ID', $compose_id);
        setProperty($action, 'COMPOSE', ['id' => $compose_id, 'mode' => $mode]);
        setProperty($action, 'CID_MAP', $is_html ? $action->cid_map($message) : []);

        $action->write_compose_attachments($message, $is_html, $body);

        $names = array_column($rcmail->list_uploaded_files($compose_id), 'name');
        sort($names);

        $rcmail->delete_uploaded_files($compose_id);

        return $names;
    }
}
