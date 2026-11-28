<?php

namespace Roundcube\Tests\Framework;

use PHPUnit\Framework\TestCase;
use Roundcube\Tests\ActionTestCase;
use Roundcube\Tests\MessageMock;

/**
 * Test class to test rcube_message class
 */
class MessageTest extends TestCase
{
    /**
     * Test format_part_body() method
     */
    public function test_format_part_body()
    {
        $part = new \rcube_message_part();
        $body = 'test';
        $result = \rcube_message::format_part_body($body, $part);

        $this->assertSame('test', $result);
    }

    /**
     * Test get_part_url() method
     */
    public function test_get_part_url()
    {
        $message = new MessageMock(10, 'Test');
        $message->mime_parts[1] = new \rcube_message_part();

        $url = $message->get_part_url(1, 'test&test=1');
        $this->assertSame('URL&_part=1&_embed=1&_mimeclass=test%26test%3D1', $url);

        $this->assertFalse($message->get_part_url(10));
    }

    /**
     * Test tnef_decode() method
     */
    public function test_tnef_decode()
    {
        $message = new MessageMock(123);
        $part = new \rcube_message_part();
        $part->mime_id = '1';

        $message->set_part_body(1, '');
        $result = $message->tnef_decode($part);

        $this->assertSame([], $result);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/body.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('winmail.1.html', $result[0]->mime_id);
        $this->assertSame('text/html', $result[0]->mimetype);
        $this->assertSame(5360, $result[0]->size);
        $this->assertStringStartsWith('<!DOCTYPE HTML', $result[0]->body);
        $this->assertSame([], $result[0]->parts);

        $message->set_part_body(1, file_get_contents(TESTS_DIR . 'src/one-file.tnef'));
        $result = $message->tnef_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('winmail.1.0', $result[0]->mime_id);
        $this->assertSame('application/octet-stream', $result[0]->mimetype);
        $this->assertSame(244, $result[0]->size);
        $this->assertStringContainsString(' Authors of', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }

    /**
     * Test uu_decode() method
     */
    public function test_uu_decode()
    {
        $message = new MessageMock(123);
        $part = new \rcube_message_part();
        $part->mime_id = '1';

        $message->set_part_body(1, '');
        $result = $message->uu_decode($part);

        $this->assertSame([], $result);

        $content = "begin 644 /dev/stdout\n" . convert_uuencode('test') . 'end';
        $message->set_part_body(1, $content);

        $result = $message->uu_decode($part);

        $this->assertCount(1, $result);
        $this->assertInstanceOf(\rcube_message_part::class, $result[0]);
        $this->assertSame('uu.1.0', $result[0]->mime_id);
        $this->assertSame('text/plain', $result[0]->mimetype);
        $this->assertSame(4, $result[0]->size);
        $this->assertSame('test', $result[0]->body);
        $this->assertSame([], $result[0]->parts);
    }

    /**
     * Test parsing of an opaque S/MIME signed message (smime-type=signed-data) (#10398)
     */
    public function test_smime_signed_data()
    {
        [$part, $body] = self::smime_part('smime-signed-data.eml', ['smime-type' => 'signed-data', 'name' => 'smime.p7m']);

        $config = \rcube::get_instance()->config;
        $prefer_html = $config->get('prefer_html');
        $config->set('prefer_html', false);
        $message = ActionTestCase::mockMessage($part, [$body]);

        // the content replaced the S/MIME part, no "encrypted" notice
        $this->assertCount(1, $message->parts);
        $this->assertSame('content', $message->parts[0]->type);
        $this->assertSame('1.1.1', $message->parts[0]->mime_id);
        $this->assertSame('text/plain', $message->parts[0]->mimetype);
        $this->assertNull($message->parts[0]->realtype);
        $this->assertSame('This message is signed, not encrypted. Grüße!', trim($message->first_text_part()));
        $this->assertSame('<html><body><p>This message is <b>signed</b>, not encrypted. Grüße!</p></body></html>', trim($message->first_html_part()));

        $this->assertSame(['1', '1.1', '1.1.1', '1.1.2', '1.2'], array_map('strval', array_keys($message->mime_parts)));
        $this->assertSame('multipart/mixed', $message->mime_parts['1']->mimetype);
        $this->assertSame('stream', $message->mime_parts['1.1.1']->encoding);

        $this->assertSame(['1.2'], array_keys($message->attachments));
        $this->assertSame('notes.txt', $message->attachments['1.2']->filename);
        $this->assertSame('attachment', $message->attachments['1.2']->disposition);
        $this->assertSame("Notes from the signed message.\r\n", $message->get_part_body('1.2'));

        // the same with the HTML part preferred
        $config->set('prefer_html', true);
        $message = ActionTestCase::mockMessage($part, [$body]);
        $config->set('prefer_html', $prefer_html);

        $this->assertCount(1, $message->parts);
        $this->assertSame('1.1.2', $message->parts[0]->mime_id);
        $this->assertSame('text/html', $message->parts[0]->mimetype);
    }

    /**
     * Test parsing of opaque S/MIME signed messages with other parameters (#10398)
     */
    public function test_smime_signed_data_detection()
    {
        // old content type, no smime-type parameter: the content type of the CMS data is checked
        [$part, $body] = self::smime_part('smime-signed-text.eml', ['name' => 'smime.p7m'], 'application/x-pkcs7-mime');
        $message = ActionTestCase::mockMessage($part, [$body]);

        $this->assertCount(1, $message->parts);
        $this->assertSame('1', $message->parts[0]->mime_id);
        $this->assertSame('text/plain', $message->parts[0]->mimetype);
        $this->assertSame('A signed plain text message.', trim($message->first_text_part()));
        $this->assertSame([], $message->attachments);

        // smime-type=signed-data, but the data is not valid
        [$part] = self::smime_part('smime-signed-text.eml', ['smime-type' => 'signed-data', 'name' => 'smime.p7m']);
        $message = ActionTestCase::mockMessage($part, [substr($body, 0, 200)]);

        $this->assertCount(1, $message->parts);
        $this->assertSame('application/pkcs7-mime', $message->parts[0]->realtype);
        $this->assertSame(['1'], array_map('strval', array_keys($message->attachments)));
        $this->assertFalse(openssl_error_string());
    }

    /**
     * Test that the body of an encrypted S/MIME message is not fetched or parsed
     */
    public function test_smime_enveloped_data()
    {
        // with smime-type=enveloped-data the storage must not be asked for the body
        [$part] = self::smime_part('smime-enveloped-data.eml', ['smime-type' => 'enveloped-data', 'name' => 'smime.p7m']);
        $message = ActionTestCase::mockMessage($part);

        $this->assertCount(1, $message->parts);
        $this->assertSame('application/pkcs7-mime', $message->parts[0]->realtype);
        $this->assertSame(['1'], array_map('strval', array_keys($message->attachments)));
        $this->assertSame('smime.p7m', $message->attachments['1']->filename);

        // without the smime-type parameter the content type of the CMS data is checked
        [$part, $body] = self::smime_part('smime-enveloped-data.eml', ['name' => 'smime.p7m']);
        $message = ActionTestCase::mockMessage($part, [$body]);

        $this->assertCount(1, $message->parts);
        $this->assertSame('application/pkcs7-mime', $message->parts[0]->realtype);
        $this->assertSame(['1'], array_map('strval', array_keys($message->attachments)));
    }

    /**
     * Returns the S/MIME part of a test message, like rcube_imap builds it from the BODYSTRUCTURE,
     * and its decoded body
     */
    private static function smime_part($file, $ctype_parameters, $mimetype = 'application/pkcs7-mime')
    {
        $file = TESTS_DIR . 'MessageRendering/data/greenmail/test-message-rendering@localhost/INBOX/' . $file;
        [, $body] = explode("\r\n\r\n", file_get_contents($file), 2);

        $part = new \rcube_message_part();
        $part->mime_id = '1';
        $part->mimetype = $mimetype;
        [$part->ctype_primary, $part->ctype_secondary] = explode('/', $mimetype);
        $part->ctype_parameters = $ctype_parameters;
        $part->encoding = 'base64';
        $part->size = strlen($body);
        $part->disposition = 'attachment';
        $part->filename = 'smime.p7m';

        return [$part, base64_decode($body)];
    }
}
