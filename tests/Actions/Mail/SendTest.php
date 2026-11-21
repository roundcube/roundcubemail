<?php

namespace Roundcube\Tests\Actions\Mail;

use PHPUnit\Framework\Attributes\DataProvider;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\MessageMock;

use function Roundcube\Tests\setProperty;

/**
 * Test class to test rcmail_action_mail_send
 */
class SendTest extends ActionTestCase
{
    /**
     * Class constructor
     */
    public function test_class()
    {
        $object = new \rcmail_action_mail_send();

        $this->assertInstanceOf(\rcmail_action::class, $object);
    }

    /**
     * Test an image referenced only from a style element in a reply, forward or draft:
     * compose (wash_html(), write_compose_attachments()), then create_message() and add_attachments()
     *
     * @dataProvider provide_style_element_inline_image_cases
     */
    #[DataProvider('provide_style_element_inline_image_cases')]
    public function test_style_element_inline_image($mode)
    {
        $this->initOutput(\rcmail_action::MODE_HTTP, 'mail', 'compose');

        $rcmail = \rcmail::get_instance();
        $image = base64_decode(\rcmail_output::BLANK_GIF);

        // the original message: an HTML part and an inline image
        $html_part = new \rcube_message_part();
        $html_part->mime_id = '1';
        $html_part->mimetype = 'text/html';
        $html_part->size = 100;

        $image_part = new \rcube_message_part();
        $image_part->mime_id = '2';
        $image_part->mimetype = 'image/gif';
        $image_part->ctype_primary = 'image';
        $image_part->ctype_secondary = 'gif';
        $image_part->disposition = 'inline';
        $image_part->content_id = 'img@test';
        $image_part->filename = 'img.gif';
        $image_part->size = strlen($image);

        $message = new MessageMock(10, 'INBOX', $mode == \rcmail_sendmail::MODE_DRAFT);
        $message->mime_parts = ['1' => $html_part, '2' => $image_part];
        $message->set_part_body('2', $image);

        $compose = new \rcmail_action_mail_compose();
        setProperty($compose, 'COMPOSE_ID', 'test');
        setProperty($compose, 'COMPOSE', ['id' => 'test', 'mode' => $mode]);
        setProperty($compose, 'MESSAGE', $message);
        setProperty($compose, 'CID_MAP', $compose->cid_map($message));

        $body = $compose->prepare_html_body('<style>p { background: url( cid:img@test ); }</style><p>Test</p>');
        $compose->write_compose_attachments($message, true, $body);

        $this->assertMatchesRegularExpression('/url\([^)]+display-attachment[^)]+\)/', $body);

        // send
        $sendmail = new \rcmail_sendmail([], ['from' => 'sender@example.org', 'charset' => RCUBE_CHARSET]);
        $attachments = $rcmail->list_uploaded_files('test');
        $mime = $sendmail->create_message([], $body, true, $attachments);
        \rcmail_action_mail_send::add_attachments($sendmail, $mime, $attachments, true);
        $raw = $mime->getMessage();

        $rcmail->delete_uploaded_files('test');

        $parts = [];
        $walk = static function ($part) use (&$walk, &$parts) {
            $parts[$part->mimetype] = $part;
            array_map($walk, $part->parts);
        };
        $walk(\rcube_mime::parse_message($raw));

        $this->assertArrayHasKey('multipart/related', $parts);
        $this->assertSame('inline', $parts['image/gif']->disposition);
        $this->assertSame($image, $parts['image/gif']->body);

        $cid = trim($parts['image/gif']->headers['content-id'], '<>');
        $html = $parts['text/html']->body;

        $this->assertStringContainsString("{ background: url(cid:{$cid}); }", $html);
        $this->assertStringNotContainsString('display-attachment', $html);
    }

    /**
     * Data for test_style_element_inline_image()
     */
    public static function provide_style_element_inline_image_cases(): iterable
    {
        return [
            [\rcmail_sendmail::MODE_REPLY],
            [\rcmail_sendmail::MODE_FORWARD],
            [\rcmail_sendmail::MODE_DRAFT],
        ];
    }

    /**
     * Test replacing inline image URLs in add_attachments()
     */
    public function test_add_attachments_references()
    {
        $url = './?_task=mail&_id=1&_action=display-attachment&_file=rcmfile1';
        $body = "<style>p { background: url({$url}); } div { background: url('{$url}'); }</style>"
            . "<p style=\"background: url('{$url}')\"><img src=\"{$url}\"><img src=\"{$url}0\"></p>";
        $attachment = ['id' => '1', 'name' => 'img.gif', 'mimetype' => 'image/gif', 'path' => $this->createTempFile('GIF')];

        $sendmail = new \rcmail_sendmail([], ['from' => 'sender@example.org']);
        $mime = new \Mail_mime("\r\n");
        $mime->setHTMLBody($body);

        \rcmail_action_mail_send::add_attachments($sendmail, $mime, [$attachment], true);

        $html = $mime->getHTMLBody();
        $this->assertMatchesRegularExpression('/cid:[0-9a-zA-Z]+@example\.org/', $html);

        $cid = preg_replace('/^.*?cid:([^)]+)\).*$/s', '$1', $html);
        $expected = "<style>p { background: url(cid:{$cid}); } div { background: url('cid:{$cid}'); }</style>"
            . "<p style=\"background: url('cid:{$cid}')\"><img src=\"cid:{$cid}\"><img src=\"{$url}0\"></p>";

        $this->assertSame($expected, $html);
    }
}
